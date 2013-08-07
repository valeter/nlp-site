#!/usr/bin/python

import boto
import sys
import os
from boto.s3.key import Key

USAGE_MESSAGE = 'Usage: ./write_file_to_aws.py <file name> <bucket> <key name> <create new bucket: true/false>'

bucket_was_created = False

def check_args(args):
	return len(args) == 5 and (args[4] == 'true' or args[4] == 'false')

def main(args):
	script_name, input_filename, bucket_name, key_name, create_new_bucket_arg = args
	create_new_bucket = create_new_bucket_arg == 'true'

	try:
		s3_connection = boto.connect_s3()
		print 'Connection to S3 service established'
		
		bucket = get_bucket(s3_connection, bucket_name, create_new_bucket)
		if (bucket == None):
			print "There's no bucket with name %s" % bucket_name
			return False
		print 'Bucket was found'

		try:
			key = Key(bucket)
			key.key = key_name
			key.set_contents_from_filename(input_filename) 
			print 'Key content was successfully set from file %s' % input_filename
		except Exception, e:
			print 'Could not set content for key %s' % key_name
			print e
			if (bucket_was_created):
				bucket.delete()
				print "Bucket %s was deleted"
			return False
	except Exception, e:
		print 'Could not establish connection to S3 service'
		print e
		return False
	return True

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
		print "Could not create bucket with name %s" % bucket_name
		print e
		return None

if (__name__ == '__main__'):
	args = sys.argv
	if (check_args(args)):
		if (main(args)):
			sys.exit()
		else:
			print 'Could not write file %s to aws!' % args[1]
			sys.exit(1)
	else:
		print USAGE_MESSAGE
	sys.exit(2)