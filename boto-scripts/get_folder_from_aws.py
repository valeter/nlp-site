#!/usr/bin/python

import boto
from boto.s3.key import Key
import sys
import os
from os import listdir
from os.path import isfile, join

USAGE_MESSAGE = 'Usage: ./get_folder_from_aws.py <bucket> <folder name/--all> <output directory>'

def check_args(args):
	return len(args) == 4

def main(args):
	script_name, bucket_name, folder_name, output_directory = args
	
	try:
		s3_connection = boto.connect_s3()
		print 'Connection to S3 service established'
		
		bucket = get_bucket(s3_connection, bucket_name)
		if (bucket == None):
			print "There's no bucket with name %s" % bucket_name
			return False
		print 'Bucket was found'

		try:
			if not os.path.exists(output_directory):
				os.makedirs(output_directory)
    			print 'Directory %s successfully created' % output_directory 
			
			keys = [ k for k in bucket.list() if (folder_name == '--all' or os.path.split(k.key)[0] == folder_name)]
			for key in keys:
				head, output_file_name = os.path.split(key.key)
				key.get_contents_to_filename(output_directory + '/' + output_file_name)
				print 'Key %s content was successfully read to file' % key.key
		except Exception, e:
			print e
			return False
	except Exception, e:
		print 'Could not establish connection to S3 service'
		print e
		return False
	return True

def get_bucket(s3_connection, bucket_name):
	try:
		bucket = s3_connection.get_bucket(bucket_name)
		return bucket
	except Exception, e:
		print 'Could not access bucket %s' % bucket_name
		print e
		return None

if (__name__ == '__main__'):
	args = sys.argv
	if (check_args(args)):
		if (main(args)):
			sys.exit()
		else:
			print 'Could not read foler %s from aws!' % args[2]
			sys.exit(1)
	else:
		print USAGE_MESSAGE
	sys.exit(2)