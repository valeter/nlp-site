#!/usr/bin/python

import boto
import sys
from boto.s3.key import Key

USAGE_MESSAGE = 'Usage: ./get_file_from_aws.py <bucket> <file name> <output filename>'

def check_args(args):
	return len(args) == 4

def get_bucket(s3_connection, bucket_name):
	try:
		bucket = s3_connection.get_bucket(bucket_name)
		return bucket
	except Exception, e:
		print 'Could not access bucket %s' % bucket_name
		print e
		return None

def main(args):
	script_name, bucket_name, key_name, output_filename = args

	try:
		s3_connection = boto.connect_s3()
		print 'Connection to S3 service established'
		bucket = get_bucket(s3_connection, bucket_name)
		if (bucket == None):
			print 'Bucket with name %s does not exists' % bucket_name
			return False
		print 'Bucket was found'
		try:
			key = Key(bucket)
			key.key = key_name
			key.get_contents_to_filename(output_filename) 
			print 'Key content was successfully read to file %s' % output_filename
		except Exception, e:
			print 'Could not read content from key %s' % key_name
			print e
			return False
	except Exception, e:
		print 'Could not establish connection to S3 service'
		print e
		return False
	return True

if (__name__ == '__main__'):
	args = sys.argv
	if (check_args(args)):
		if (main(args)):
			sys.exit()
		else:
			print 'Could not get file %s from aws!' % args[3]
			sys.exit(1)
	else:
		print USAGE_MESSAGE
	sys.exit(2)