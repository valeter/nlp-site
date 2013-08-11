#!/usr/bin/python
# -*- coding: utf-8 -*-

import sys
import time
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
			'-o', '/mnt/bcorpus/' + taskid])

	tika_step = JarStep(name='tika-' + taskid,
		jar='s3n://' + bucket_name + '/behemoth/behemoth-tika.jar',
		step_args=['com.digitalpebble.behemoth.tika.TikaDriver',
			'-i', '/mnt/bcorpus' + taskid,
			'-o', '/mnt/tcorpus' + taskid])

	uima_step = JarStep(name='uima-' + taskid,
		jar='s3n://' + bucket_name + '/behemoth/behemoth-uima.jar',
		step_args=['com.digitalpebble.behemoth.uima.UIMADriver',
			'/mnt/tcorpus' + taskid,
			'/mnt/ucorpus' + taskid,
			'/mnt/pipeline.pear'])

	steps = []
	steps.append(preprocessing_step)
	steps.append(tika_step)
	steps.append(uima_step)

	return steps

def wait_for_terminating():
	l = 0
	max_time = 1300
	wait_time = 0
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

			wait_time += time.time() - cur_time
			if wait_time > max_time:
				break 
		except Exception, e:
			l = l + 1
			if l == 10:
				break
			return False
	return False

def run_command(command):
	try:
		process = subprocess.Popen(command.split(), stdout=subprocess.PIPE)
		output = process.communicate()[0]
		return True
	except Exception, e:
		print e
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
		return True
	except Exception, e:
		return False

def path_leaf(path):
    head, tail = ntpath.split(path)
    return tail or ntpath.basename(head)

def main(args):
	script_name, cluster_id, filename = args

	if not write_file_to_aws(filename, path_leaf(filename)):
		print "error i"
		return

	if not add_steps(cluster_id, path_leaf(filename)):
		print "error c"
		return

	data = wait_for_terminating()
	if (data == False):
		print "error w"
		return

	if not delete_key(path_leaf(filename)):
		print "error d"
		return

	print data

if __name__ == "__main__":
	args = sys.argv
	main(args)
	sys.exit()