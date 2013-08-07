#!/usr/bin/python

import boto
import subprocess
import sys
import time
from boto.emr.connection import EmrConnection
from boto.emr.step import JarStep
from boto.emr.bootstrap_action import BootstrapAction

USAGE_MESSAGE = 'Usage: ./run_cluster.py' 

def check_args(args):
	return len(args) == 1

def main(args):
	script_name = args
	for i in range(2, 3, 2):
		start_time = time.time()


		bucket_name = 'nlp-' + str(i).strip()

		emr_connection = EmrConnection()

		preprocessing_steps = []
		for j in xrange(12, 13, 12):
			preprocessing_steps.append(JarStep(name='prerocessing-' + str(i).strip(),
				jar='s3n://nlp-' + str(i).strip() + '/init/behemoth-core.jar',
				step_args=['com.digitalpebble.behemoth.util.CorpusGenerator',
					'-i', 's3n://nlp-' + str(i).strip() + '/' + str(j).strip() + '/texts',
					'-o', 's3n://nlp-' + str(i).strip() + '/' + str(j).strip() + '/bcorpus']))

		tika_steps = []
		for j in xrange(12, 13, 12):
			tika_steps.append(JarStep(name='tika-' + str(i).strip(),
				jar='s3n://nlp-' + str(i).strip() + '/init/behemoth-tika.jar',
				step_args=['com.digitalpebble.behemoth.tika.TikaDriver',
					'-i', 's3n://nlp-' + str(i).strip() + '/' + str(j).strip() + '/bcorpus',
					'-o', 's3n://nlp-' + str(i).strip() + '/' + str(j).strip() + '/tcorpus']))

		copy_jar_steps = []
		for j in xrange(12, 13, 12):
			copy_jar_steps.append(JarStep(name='copy-jar-' + str(i).strip(),
				jar='s3n://nlp-' + str(i).strip() + '/init/copy-to-hdfs.jar',
				step_args=['s3n://nlp-' + str(i).strip() + '/init/pipeline.pear',
					'/mnt/pipeline.pear']))

		uima_steps = []
		for j in xrange(12, 13, 12):
			uima_steps.append(JarStep(name='uima-' + str(i).strip(),
				jar='s3n://nlp-' + str(i).strip() + '/init/behemoth-uima.jar',
				step_args=['com.digitalpebble.behemoth.uima.UIMADriver',
					's3n://nlp-' + str(i).strip() + '/' + str(j).strip() + '/tcorpus',
					'/mnt/ucorpus',
					'/mnt/pipeline.pear']))

		steps = []
		steps.extend(preprocessing_steps
		steps.extend(tika_steps)
		steps.extend(copy_jar_steps)
		steps.extend(uima_steps)
		steps.extend(extract_result_steps)

		hadoop_params = ['-m','mapred.tasktracker.map.tasks.maximum=1',
		          '-m', 'mapred.child.java.opts=-Xmx10g']
		configure_hadoop_action = BootstrapAction('configure_hadoop', 's3://elasticmapreduce/bootstrap-actions/configure-hadoop', hadoop_params)

		jobid = emr_connection.run_jobflow(name='nlp-cloud-' + str(i).strip(),
			log_uri='s3://nlp-' + str(i).strip() + '/jobflow_logs',
			master_instance_type='m2.xlarge',
			slave_instance_type='m2.xlarge',
			num_instances=i,
			keep_alive=False,
			enable_debugging=False,
			bootstrap_actions=[configure_hadoop_action],
			hadoop_version='1.0.3',
			steps=steps)

		termination_statuses = [u'COMPLETED', u'FAILED', u'TERMINATED']
		while True:
			time.sleep(5)
			status = emr_connection.describe_jobflow(jobid) 
			if status.state in termination_statuses:
				print 'Job finished for %s nodes' % i
				break


		print time.time() - start_time, ' seconds elapsed'



	return True

if (__name__ == '__main__'):
	args = sys.argv
	if (check_args(args)):
		if (main(args)):
			sys.exit()
			print 'Work successfully finished'
		else:
			print 'Could not finish work'
			sys.exit(1)
	else:
		print USAGE_MESSAGE
	sys.exit(2)