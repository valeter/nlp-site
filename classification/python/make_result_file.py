#!/usr/bin/python
# -*- coding: utf-8 -*-

import os
from os import listdir
from os.path import isfile, join, dirname, abspath
import time
import boto
import subprocess
import sys
import time
from boto.emr.connection import EmrConnection
from boto.emr.step import JarStep
from boto.emr.bootstrap_action import BootstrapAction
from boto.s3.key import Key
import MySQLdb
import billing
#sys.path.append(os.path.abspath("/root/billing-python/billing.py"))
#import billing

def prepare_s3(bucket_name):
	try:
		s3_connection = boto.connect_s3()
		print 'Connection to S3 service established'
		
		bucket = get_bucket(s3_connection, bucket_name, create_and_return_bucket)
		if (bucket == None):
			print "There's no bucket with name %s" % bucket_name
			return False
		print 'Bucket was found'
	except Exception, e:
		print 'Could not establish connection to S3 service'
		print e
		return False

	mp = dirname(abspath(__file__)) + '/../../'
	bsp = mp + 'boto-scripts/'
	wfsp = bsp + 'write_file_to_aws.py '

	write_init_folder_commands = []
	write_init_folder_commands.append(wfsp + mp + 'classification/pipeline.pear ' + bucket_name + ' pipeline.pear false')
	write_init_folder_commands.append(wfsp + mp + 'behemoth/behemoth-core-1.1-SNAPSHOT-job.jar ' + bucket_name + ' behemoth/behemoth-core.jar false')
	write_init_folder_commands.append(wfsp + mp + 'behemoth/behemoth-tika-1.1-SNAPSHOT-job.jar ' + bucket_name + ' behemoth/behemoth-tika.jar false')
	write_init_folder_commands.append(wfsp + mp + 'behemoth/behemoth-uima-1.1-SNAPSHOT-job.jar ' + bucket_name + ' behemoth/behemoth-uima.jar false')
	write_init_folder_commands.append(wfsp + mp + 'copy-to-hdfs.jar ' + bucket_name + ' copy-to-hdfs.jar false')
	for command in write_init_folder_commands:
		run_command(command)

def get_bucket(s3_connection, bucket_name, create_new_bucket):
	bucket = lookup_and_return_bucket(s3_connection, bucket_name)
	if (bucket == None and create_new_bucket):
		bucket = create_and_return_bucket(s3_connection, bucket_name)
	return bucket

def lookup_and_return_bucket(s3_connection, bucket_name):
	try:
		bucket = s3_connection.lookup(bucket_name)
		return bucket
	except Exception, e:
		print 'Could not access bucket %s' % bucket_name
		print e
		return None

def create_and_return_bucket(s3_connection, bucket_name):
	try:
		bucket = s3_connection.create_bucket(bucket_name)
		bucket_was_created = True
		return bucket
	except Exception, e:
		print e
		return None


class Task(object):
	foldername = ""
	filename = ""
	short_filename = ""
	old_jobflow = -1
	old_num = 1
	start_cluster = True
	jobflow_id = -1
	bucket_name = "udk-bucket"
	emr_connection = EmrConnection()
	s3_connection = boto.connect_s3()
	jffile = dirname(abspath(__file__)) + '/jffile'

	def __init__(self, f_name):
		self.foldername = dirname(abspath(__file__)) + "/../file-upload/server/php/files/"
		self.filename =  self.foldername + f_name
		self.short_filename = f_name

	def read_old_jobflow_params(self):		
		with open(self.jffile, 'r') as f:
			self.old_jobflow = f.readline().strip()
			self.old_num = int(f.readline().strip())

		try:
			termination_statuses = [u'COMPLETED', u'FAILED', u'TERMINATED', u'SHUTTING_DOWN']
			
			old_flow = self.emr_connection.describe_jobflow(self.old_jobflow)
			if old_flow == None:
				print "Old flow not found"
			if (old_flow.state not in termination_statuses):
				print "Found working old jobflow"
				self.start_cluster = False
				self.jobflow_id = self.old_jobflow
				with open(self.jffile, 'w') as f:
					f.write(str(self.jobflow_id) +'\n')
					f.write(str(self.old_num + 1) +'\n')
				print "Connecting to old jobflow"
			else:
				self.old_num = 0
				print "Could not connect to old jobflow"
		except Exception, e:
			self.old_num = 0
			print "Error while connectiong to old jobflow: " + str(e)

	def write_file_to_aws(self, key_name):
		mp = dirname(abspath(__file__)) + '/../../'
		bsp = mp + 'boto-scripts/'
		wfsp = bsp + 'write_file_to_aws.py '
		write_input_files_command = wfsp + self.filename + ' ' + self.bucket_name + ' ' + key_name + ' false'
		run_command(write_input_files_command)

	def get_steps(self, taskid, input_folder):
		preprocessing_step = JarStep(name='prerocessing-' + taskid,
			jar='s3n://' + self.bucket_name + '/behemoth/behemoth-core.jar',
			step_args=['com.digitalpebble.behemoth.util.CorpusGenerator',
				'-i', 's3n://' + self.bucket_name + '/' + input_folder,
				'-o', '/mnt/bcorpus' + taskid])

		tika_step = JarStep(name='tika-' + taskid,
			jar='s3n://' + self.bucket_name + '/behemoth/behemoth-tika.jar',
			step_args=['com.digitalpebble.behemoth.tika.TikaDriver',
				'-i', '/mnt/bcorpus' + taskid,
				'-o', '/mnt/tcorpus' + taskid])

		copy_jar_step = JarStep(name='copy-jar-' + taskid,
			jar='s3n://' + self.bucket_name + '/copy-to-hdfs.jar',
			step_args=['s3n://' + self.bucket_name + '/pipeline.pear',
				'/mnt/pipeline.pear'])

		uima_step = JarStep(name='uima-' + taskid,
			jar='s3n://' + self.bucket_name + '/behemoth/behemoth-uima.jar',
			step_args=['com.digitalpebble.behemoth.uima.UIMADriver',
				'/mnt/tcorpus' + taskid,
				'/mnt/ucorpus' + taskid,
				'/mnt/pipeline.pear'])

		steps = []
		steps.append(preprocessing_step)
		steps.append(tika_step)
		if self.start_cluster:
			steps.append(copy_jar_step)
		steps.append(uima_step)

		return steps

	def start_hadoop_cluster(self, steps):
		print "Starting new jobflow"
		hadoop_params = ['-m','mapred.tasktracker.map.tasks.maximum=1',
		          '-m', 'mapred.child.java.opts=-Xmx10g']
		configure_hadoop_action = BootstrapAction('configure_hadoop', 's3://elasticmapreduce/bootstrap-actions/configure-hadoop', hadoop_params)

		self.jobflow_id = self.emr_connection.run_jobflow(name='udk',
			log_uri='s3://' + self.bucket_name + '/jobflow_logs',
			master_instance_type='m2.xlarge',
			slave_instance_type='m2.xlarge',
			num_instances=2,
			enable_debugging=False,
			bootstrap_actions=[configure_hadoop_action],
			hadoop_version='1.0.3',
			steps=steps)

		with open(self.jffile, 'w') as f:
			f.write(str(self.jobflow_id) +'\n')
			f.write(str(self.old_num + 1) +'\n')
		
		print "Jobflow %s started" % self.jobflow_id

	def wait_for_terminating(self):
		l = 0
		while(True):
			try:
				db = MySQLdb.connect(host="192.241.150.164", user="root", passwd="tatishev5.4", db="nlp_systems", charset='utf8')
				cursor = db.cursor()			
				sql = """SELECT * FROM classification"""
				cursor.execute(sql)
				data =  cursor.fetchall()
				if len(data) != 0:
					break

				db.close()
			except Exception, e:
				l = l + 1
				if l == 10:
					break
				print "DB error: " + str(e)

	def process(self):
		try:
			print "Processing %s" % str(self.short_filename)
			
			self.read_old_jobflow_params()

			taskid = str(self.old_num + 1).strip()
			input_folder = 'input' + taskid
			self.write_file_to_aws(input_folder + "/" + self.short_filename)
			
			start_time = time.time()

			steps = self.get_steps(taskid, input_folder)
			if self.start_cluster:
				pass
				#self.start_hadoop_cluster(steps)
			else:
				pass
				#self.emr_connection.add_jobflow_steps(self.jobflow_id, steps)

			self.wait_for_terminating()

			secs = time.time() - start_time
			print secs, ' seconds elapsed'
			
			billing = billing.Billing()
			billing.connect()
			billing.add_record(work_time_seconds=secs, nodes=2, node_minute_price_cents=9, service='classification')
			billing.close()
			
			bucket = self.s3_connection.get_bucket(self.bucket_name)
			bucket.delete_key(input_folder + '/' + self.short_filename)
			
			run_ie(self.short_filename)
		except Exception, e:
			print "Unsupposed error: " + str(e)

def run_ie(filename):
	ie_root = dirname(abspath(__file__)) + '/../ie/'
	input_filename = dirname(abspath(__file__)) + "/../file-upload/server/php/files/" + filename
	result_filename = dirname(abspath(__file__)) + "/../file-upload/server/php/files/" + filename + ".nlpresult"
	ie_cmd = "java -jar " + ie_root + "udk.jar " + input_filename + "  "+ ie_root + "microdicts " + ie_root + "dict/ " + result_filename
	print "Running IE on file: " + filename
	print ie_cmd
	run_command(ie_cmd)

def run_command(command):
	try:
		process = subprocess.Popen(command.split(), stdout=subprocess.PIPE)
		output = process.communicate()[0]
		print output
	except Exception, e:
		print "Error while executing command: " + str(command)

def main(args):
	script_name, filename = args
	run_ie(filename)
	#task = Task(filename)
	#task.process()

if __name__ == "__main__":
	args = sys.argv
	main(args)
	sys.exit()