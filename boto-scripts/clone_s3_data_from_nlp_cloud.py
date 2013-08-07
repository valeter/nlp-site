#!/usr/bin/python

import boto
import subprocess
import sys

USAGE_MESSAGE = 'Usage: ./prepare_s3_for_cluster.py' 

"""
result bucket catalogue structure:

-init
	-pipeline.pear
	-behemoth-core.jar
	-behemoth-tika.jar
	-behemoth-uima.jar
	-copy-to-hdfs.jar
-12
	-texts
		-besy1.txt
		...
		-besy12.txt
-24
	...
-36
	...
...
-96 
	-texts
		-besy1.txt
		...
		-besy96.txt
"""

def check_args(args):
	return len(args) == 1

def run_command(command):
	print command
	process = subprocess.Popen(command.split(), stdout=subprocess.PIPE)
	output = process.communicate()[0]
	print output


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

def main(args):
	script_name = args
	for i in range(2, 21, 2):
		bucket_name = 'nlp-' + str(i).strip()

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

		write_init_folder_commands = []
		write_init_folder_commands.append('./clone_key_n_times.py nlp-cloud pear/v0002.pear ' + bucket_name + ' init/pipeline.pear 1')
		write_init_folder_commands.append('./clone_key_n_times.py nlp-cloud behemoth/behemoth-core-1.1-SNAPSHOT-job.jar ' + bucket_name + ' init/behemoth-core.jar 1')
		write_init_folder_commands.append('./clone_key_n_times.py nlp-cloud behemoth/behemoth-tika-1.1-SNAPSHOT-job.jar ' + bucket_name + ' init/behemoth-tika.jar 1')
		write_init_folder_commands.append('./clone_key_n_times.py nlp-cloud behemoth/behemoth-uima-1.1-SNAPSHOT-job.jar ' + bucket_name + ' init/behemoth-uima.jar 1')
		write_init_folder_commands.append('./write_file_to_aws.py /home/valter/copy-to-hdfs.jar ' + bucket_name + ' init/copy-to-hdfs.jar false')
		write_besy_files_commands = []
		clone_besy_commands = []
		for j in xrange(12, 97, 12):
			clone_besy_commands.append('./clone_key_n_times.py nlp-cloud c1/besy.txt ' + bucket_name + ' ' + str(j).strip() + '/texts/besy.txt ' + str(j).strip())
			
		run_command(write_init_folder_commands[4])	
		"""
		for command in write_init_folder_commands:
			run_command(command)
		for command in clone_besy_commands:
			run_command(command)
		"""
	return True

if (__name__ == '__main__'):
	args = sys.argv
	if (check_args(args)):
		if (main(args)):
			sys.exit()
			print 'Preparation successfully finished'
		else:
			print 'Could not finish preparation'
			sys.exit(1)
	else:
		print USAGE_MESSAGE
	sys.exit(2)