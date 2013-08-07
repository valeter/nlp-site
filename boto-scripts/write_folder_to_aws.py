#!/usr/bin/python

import boto
from boto.s3.key import Key
import sys
import os
from os import listdir
from os.path import isfile, join

USAGE_MESSAGE = 'Usage: ./write_folder_to_aws.py <folder name> <bucket> <create new bucket: true/false>'

bucket_was_created = False

def check_args(args):
	return len(args) == 4 and (args[3] == 'true' or args[3] == 'false')

def write_key_to_bucket(bucket, key_name, input_file):
	try:
		key = Key(bucket)
		key.key = key_name
		key.set_contents_from_filename(input_file)
		print 'Key content was successfully set from file %s' % key_name
	except Exception, e:
		print 'Could not set content for key %s' % key_name
		print e

def main(args):
	script_name, folder_name, bucket_name, create_new_bucket_arg = args
	create_new_bucket = create_new_bucket_arg == 'true'

	try:
		s3_connection = boto.connect_s3()
		print 'Connection to S3 service established'
		
		bucket = get_bucket(s3_connection, bucket_name, create_and_return_bucket)
		if (bucket == None):
			print "There's no bucket with name %s" % bucket_name
			return False
		print 'Bucket was found'

		try:
			files = [ f for f in listdir(folder_name) if isfile(join(folder_name,f)) ]
			head, term_folder = os.path.split(folder_name)
			for f in files:
				head, file_name = os.path.split(f)
				write_key_to_bucket(bucket, '/' + term_folder + '/' + file_name, folder_name + '/' + file_name)
		except Exception, e:
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
			print 'Could not write folder %s to aws!' % args[1]
			sys.exit(1)
	else:
		print USAGE_MESSAGE
	sys.exit(2)