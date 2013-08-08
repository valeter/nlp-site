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

def get_result():
	time.sleep(5)
	#prepare_s3()
	run_jobs()

def delete_bucket():
	mp = dirname(abspath(__file__)) + '/../../'
	bsp = mp + 'boto-scripts/'
	command = bsp + "delete_bucket.py " + b_name
	run_command(command)

def run_ie(filename):
	ie_root = dirname(abspath(__file__)) + '/../ie/'
	result_filename = dirname(abspath(__file__)) + "/../file-upload/server/php/files/" + filename + ".nlpresult"
	ie_cmd = "java -jar " + ie_root + "InformationExtractionClassifier-0.1.jar" + " " + ie_root +"dict/ " + result_filename
	run_command(ie_cmd)

def run_command(command):
	print command
	try:
		process = subprocess.Popen(command.split(), stdout=subprocess.PIPE)
		output = process.communicate()[0]
		print output
	except Exception, e:
		print "Error while executing command: " + str(command)

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

b_name = "udk-bucket"

def prepare_s3():
	b_name = 'udk-bucket'
	try:
		s3_connection = boto.connect_s3()
		print 'Connection to S3 service established'
		
		bucket = get_bucket(s3_connection, b_name, create_and_return_bucket)
		if (bucket == None):
			print "There's no bucket with name %s" % b_name
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
	write_init_folder_commands.append(wfsp + mp + 'classification/pipeline.pear ' + b_name + ' pipeline.pear false')
	write_init_folder_commands.append(wfsp + mp + 'behemoth/behemoth-core-1.1-SNAPSHOT-job.jar ' + b_name + ' behemoth/behemoth-core.jar false')
	write_init_folder_commands.append(wfsp + mp + 'behemoth/behemoth-tika-1.1-SNAPSHOT-job.jar ' + b_name + ' behemoth/behemoth-tika.jar false')
	write_init_folder_commands.append(wfsp + mp + 'behemoth/behemoth-uima-1.1-SNAPSHOT-job.jar ' + b_name + ' behemoth/behemoth-uima.jar false')
	write_init_folder_commands.append(wfsp + mp + 'copy-to-hdfs.jar ' + b_name + ' copy-to-hdfs.jar false')
	for command in write_init_folder_commands:
		run_command(command)

def run_jobs():
	input_path = dirname(abspath(__file__)) + "/../file-upload/server/php/files/"
	input_files = [ f for f in listdir(input_path) if isfile(join(input_path,f)) ]

	first_time = True
	i = 1
	jobid = -1
	oldnum = 1

	jffile = dirname(abspath(__file__)) + '/jffile'
	with open(jffile, 'r') as f:
		oldid = f.readline().strip()
		oldnum = int(f.readline().strip())

	emr_connection = EmrConnection()
	s3_connection = boto.connect_s3()
	termination_statuses = [u'COMPLETED', u'FAILED', u'TERMINATED']
	try:
		oldflow = emr_connection.describe_jobflow(oldid)
		if (oldflow.state not in termination_statuses):
			print "Found working old jobflow %s" + str(oldflow)
			first_time = False
			jobid = oldid
			with open(jffile, 'w') as f:
				f.write(str(jobid) +'\n')
				f.write(str(oldnum + 1) +'\n')
			print "Connectiong to old jobflow %s" + str(jobid)
		else:
			oldnum = 0
			print "Could not connect to old jobflow"
	except Exception, e:
		oldnum = 0
		print "Error while connectiong to old jobflow: " + str(e)

	try:
		for input_file in input_files:
			if (input_file.startswith('.') or ".nlpresult" in str(input_file)):
				print str(input_file) + " skipped"
				continue

			print "Processing %s" % str(input_file)
			taskid = str(oldnum + 1).strip() + str(i).strip()
			input_folder = 'input' + taskid
			mp = dirname(abspath(__file__)) + '/../../'
			bsp = mp + 'boto-scripts/'
			wfsp = bsp + 'write_file_to_aws.py '
			write_input_files_command = wfsp + input_path + input_file + ' ' + b_name + ' ' + input_folder + '/' + input_file + ' false'
			run_command(write_input_files_command)

			start_time = time.time()

			bucket_name = b_name

			preprocessing_step = JarStep(name='prerocessing-' + taskid,
				jar='s3n://' + bucket_name + '/behemoth/behemoth-core.jar',
				step_args=['com.digitalpebble.behemoth.util.CorpusGenerator',
					'-i', 's3n://' + bucket_name + '/' + input_folder,
					'-o', '/mnt/bcorpus' + taskid])

			tika_step = JarStep(name='tika-' + taskid,
				jar='s3n://' + bucket_name + '/behemoth/behemoth-tika.jar',
				step_args=['com.digitalpebble.behemoth.tika.TikaDriver',
					'-i', '/mnt/bcorpus' + taskid,
					'-o', '/mnt/tcorpus' + taskid])

			copy_jar_step = JarStep(name='copy-jar-' + taskid,
				jar='s3n://' + bucket_name + '/copy-to-hdfs.jar',
				step_args=['s3n://' + bucket_name + '/pipeline.pear',
					'/mnt/pipeline.pear'])

			uima_step = JarStep(name='uima-' + taskid,
				jar='s3n://' + bucket_name + '/behemoth/behemoth-uima.jar',
				step_args=['com.digitalpebble.behemoth.uima.UIMADriver',
					'/mnt/tcorpus' + taskid,
					'/mnt/ucorpus' + taskid,
					'/mnt/pipeline.pear'])

			steps = []
			steps.append(preprocessing_step)
			steps.append(tika_step)
			steps.append(copy_jar_step)
			steps.append(uima_step)

			if (first_time):
				print "Starting new jobflow"
				hadoop_params = ['-m','mapred.tasktracker.map.tasks.maximum=1',
				          '-m', 'mapred.child.java.opts=-Xmx10g']
				configure_hadoop_action = BootstrapAction('configure_hadoop', 's3://elasticmapreduce/bootstrap-actions/configure-hadoop', hadoop_params)

				jobid = emr_connection.run_jobflow(name='udk',
					log_uri='s3://' + bucket_name + '/jobflow_logs',
					master_instance_type='m2.xlarge',
					slave_instance_type='m2.xlarge',
					num_instances=2,
					keep_alive=True,
					enable_debugging=False,
					bootstrap_actions=[configure_hadoop_action],
					hadoop_version='1.0.3',
					steps=steps)
				first_time = False
				with open(jffile, 'w') as f:
					f.write(jobid +'\n')
					f.write(str(1) +'\n')
				print "Jobflow %s started" % jobid 
			else:
				emr_connection.add_jobflow_steps(jobid, steps)
			i = i + 1

			termination_statuses = [u'COMPLETED', u'FAILED', u'TERMINATED', u'WAITING']
			exit_status = None
			while True:
				time.sleep(30)
				status = emr_connection.describe_jobflow(jobid)
				if status.state in termination_statuses:
					print 'Job finished for %s nodes' % str(i)
					exit_status = status
					break
			print time.time() - start_time, ' seconds elapsed'

			bucket = s3_connection.get_bucket(bucket_name)
			bucket.delete_key(input_folder + '/' + input_file)
			if (bucket.get_key(input_folder) != None):
				bucket.delete_key(input_folder)

			if (exit_status != None and exit_status.state != u'WAITING'):
				print "Jobflow terminated too soon with status %s" % exit_status.state
				break
			try:
				run_ie(input_file)
			except Exception, e:
				print "Error on ie module: " + str(e)
	except Exception, e:
		print "Unsupposed error: " + str(e)

	try:
		emr_connection.terminate_jobflow(jobid)
		print 'Jobflow terminated'
	except Exception, e:
		print "Error while terminating jobflow: " + str(e)


if __name__ == "__main__":
    get_result()