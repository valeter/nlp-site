#!/usr/bin/python
# -*- coding: utf-8 -*-

import sys
import time
import boto
import os
from os.path import dirname, abspath
from boto.emr.connection import EmrConnection
from boto.emr.step import JarStep
from boto.s3.key import Key
import MySQLdb
import ntpath
import subprocess

bucket_name = "udk-bucket"

def write_file_to_aws(filename, key_name):
	try:
		mp = dirname(abspath(__file__)) + '/../../'
		bsp = mp + 'boto-scripts/'
		wfsp = bsp + 'write_file_to_aws.py '
		write_input_files_command = wfsp + filename + ' ' + bucket_name + ' ' + key_name + ' false'
		return run_command(write_input_files_command)
	except Exception, e:
		return False

def get_steps(taskid, input_key):
	preprocessing_step = JarStep(name='prerocessing-' + taskid,
		jar='s3n://' + bucket_name + '/behemoth/behemoth-core.jar',
		step_args=['com.digitalpebble.behemoth.util.CorpusGenerator',
			'-i', 's3n://' + bucket_name + '/' + input_key,
			'-o', 's3n://' + bucket_name + '/b' + taskid])

	tika_step = JarStep(name='tika-' + taskid,
		jar='s3n://' + bucket_name + '/behemoth/behemoth-tika.jar',
		step_args=['com.digitalpebble.behemoth.tika.TikaDriver',
			'-i', 's3n://' + bucket_name + '/b' + taskid,
			'-o', '/mnt/t' + taskid])

	uima_step = JarStep(name='uima-' + taskid,
		jar='s3n://' + bucket_name + '/behemoth/behemoth-uima.jar',
		step_args=['com.digitalpebble.behemoth.uima.UIMADriver',
			'/mnt/t' + taskid,
			'/mnt/u' + taskid,
			'/mnt/pipeline.pear'])

	steps = []
	steps.append(preprocessing_step)
	steps.append(tika_step)
	steps.append(uima_step)

	return steps

def get_cluster_status(cluster_id):
	try:
		emr_connection = EmrConnection()
		flow = emr_connection.describe_jobflow(cluster_id)
		if flow == None:
			return "none"
		return flow.state
	except Exception, e:
		return "none"

def wait_for_terminating(cluster_id):
	l = 0
	max_time = 1300
	wait_time = 0
	i = 0
	while(True):
		try:
			cur_time = time.time()

			db = MySQLdb.connect(host="192.241.150.164", user="root", passwd="tatishev5.4", db="nlp_systems", charset='utf8')
			cursor = db.cursor()			
			sql = """SELECT * FROM classification"""
			cursor.execute(sql)
			data = cursor.fetchall()
			if len(data) != 0:
				return data

			db.close()
			time.sleep(5)
			status = get_cluster_status(cluster_id)
			if status != u"RUNNING":
				i += 1
				if i >= 20:
					break

			wait_time += time.time() - cur_time
			if wait_time > max_time:
				break 
		except Exception, e:
			l = l + 1
			if l >= 10:
				return False
	return False

def run_command(command):
	try:
		process = subprocess.Popen(command.split(), stdout=subprocess.PIPE)
		output = process.communicate()[0]
		return True
	except Exception, e:
		return False

def add_steps(cluster_id, key):
	try:
		emr_connection = EmrConnection()
		emr_connection.add_jobflow_steps(cluster_id, get_steps(key, key))
		return True
	except Exception, e:
		return False

def delete_key(key):
	try:
		s3_connection = boto.connect_s3()
		bucket = s3_connection.get_bucket(bucket_name)
		bucket.delete_key(key)
		bucket.delete_key('b' + key)
		return True
	except Exception, e:
		return False

def path_leaf(path):
    head, tail = ntpath.split(path)
    return tail or ntpath.basename(head)

def run_ie(filename):
	try:
		ie_root = dirname(abspath(__file__)) + '/../ie/'
		input_filename = filename
		result_filename = filename + ".nlpresult"
		ie_cmd = "java -jar " + ie_root + "InformationExtractionClassifier-0.1.jar " + ie_root + "dict/ " + result_filename
		run_command(ie_cmd)
		return True
	except Exception, e:
		 return False

def print_result(filename):
	with open(filename, "r") as f:
		print f.readline().strip()
		print f.readline().strip()

def main(args):
	start = time.time()

	script_name, cluster_id, filename, file_id, num_nodes = args
	num_nodes = int(num_nodes)

	if not write_file_to_aws(filename, file_id):
		print "error i"
		return

	if not add_steps(cluster_id, file_id):
		print "error c"
		return
	
	data = wait_for_terminating(cluster_id)
	if (data == False):
		print "error w"
		return

	if not delete_key(file_id):
		print "error d"
		return

	if not run_ie(filename):
		print "error e"
		return

	end = time.time()
	total = end - start

	try:
		import billing
		billing = billing.Billing()
		billing.connect()
		billing.add_record(work_time_seconds=total, nodes=num_nodes, node_minute_price_cents=9, service='classification')
		billing.close()
	except Exception, e:
		pass

	step_num = 4
	try:
		with open(dirname(abspath(__file__)) + '/step', 'r') as f:
			c_id = f.readline().strip()
			c_step = f.readline().strip()

			if (c_id == cluster_id):
				step_num = int(c_step) + 3

		with open(dirname(abspath(__file__)) + '/step', 'w') as f:
			f.write(cluster_id + '\n')
			f.write(str(step_num) + '\n')
	except Exception, e:
		step_num = 4

	try:
		import hadoop
		hadoop = hadoop.Hadoop()
		hadoop.connect()
		import aws_reader
		aws_reader = aws_reader.Reader("udk-bucket")
		log_str = ""
		i = 0
		while (i < 24):
			log_str = aws_reader.read("jobflow_logs/" + cluster_id + "/steps/" + str(step_num - 2) + "/syslog")
			if (len(log_str) != 0):
				break
			time.sleep(5)
			i += 1

		if (len(log_str) == 0):
			try:
				hadoop = hadoop.Hadoop()
				hadoop.connect()
				log_str = aws_reader.read("jobflow_logs/" + cluster_id + "/steps/" + str(step_num - 2) + "/syslog")

				if (len(log_str) == 0):
					raise Exception()

				hadoop.add_record(log_str)
				hadoop.close()
			except Exception, e:
				pass

			if (len(log_str) == 0):
				raise Exception()

		hadoop.add_record(log_str)
		hadoop.close()
	except Exception, e:
		try:
			import hadoop
			hadoop = hadoop.Hadoop()
			hadoop.connect()
			from datetime import datetime
			hadoop.add_record(str(datetime.now()) + "  Cannot read log at: " + "jobflow_logs/" + cluster_id + "/steps/4/syslog")
			hadoop.close()
		except Exception, e:
			pass

	try:
		sys.stdout = os.devnull
		sys.stderr = os.devnull
		import amazon_stat
		amazon_stat = amazon_stat.AmazonStats()
		amazon_stat.connect()
		amazon_stat.add_record("m2xlarge", num_nodes, total)
		amazon_stat.close()
	except:
		pass

	sys.stdout = sys.__stdout__
	sys.stderr = sys.__stderr__

	print_result(filename + ".nlpresult")

if __name__ == "__main__":
	args = sys.argv
	main(args)
	sys.exit()