#!/usr/bin/python
# -*- coding: utf-8 -*-

import sys
from boto.emr.connection import EmrConnection
from boto.emr.bootstrap_action import BootstrapAction
from boto.emr.step import JarStep

def start_hadoop_cluster(nodenum):
	try:
		hadoop_params = ['-m','mapred.tasktracker.map.tasks.maximum=1',
		          '-m', 'mapred.child.java.opts=-Xmx10g']
		configure_hadoop_action = BootstrapAction('configure_hadoop', 's3://elasticmapreduce/bootstrap-actions/configure-hadoop', hadoop_params)

		emr_connection = EmrConnection()
		bucket_name = "udk-bucket"
		steps = []
		copy_jar_step = JarStep(name='copy-jar',
			jar='s3n://' + bucket_name + '/copy-to-hdfs.jar',
			step_args=['s3n://' + bucket_name + '/pipeline.pear',
				'/mnt/pipeline.pear'])
		steps.append(copy_jar_step)

		jobflow_id = emr_connection.run_jobflow(name='udk',
			log_uri='s3://udk-bucket/jobflow_logs',
			master_instance_type='m2.xlarge',
			slave_instance_type='m2.xlarge',
			num_instances=nodenum,
			keep_alive=True,
			enable_debugging=False,
			bootstrap_actions=[configure_hadoop_action],
			hadoop_version='1.0.3',
			steps=steps)
		emr_connection.set_termination_protection(jobflow_id, True)
		
		return jobflow_id
	except Exception, e:
		return "none" 

def main(args):
	script_name, nodenum = args
	nodenum = int(nodenum.strip())
	print start_hadoop_cluster(nodenum)

if __name__ == "__main__":
	args = sys.argv
	main(args)
	sys.exit()