#!/usr/bin/python

import boto
from boto.s3.key import Key
import sys
import os
from os import listdir
from os.path import isfile, join

USAGE_MESSAGE = 'Usage: ./delete_bucket.py <bucket>'


def check_args(args):
	return len(args) == 2

def main(args):
	script_name, bucket_name = args
	
	try:
		s3_connection = boto.connect_s3()
		print 'Connection to S3 service established'
		
		bucket = get_bucket(s3_connection, bucket_name)
		if (bucket == None):
			print "There's no bucket with name %s" % bucket_name
			return False
		print 'Bucket was found'

		try:
			keys = bucket.list()
			for key in keys:
				key_name = key.key
				key.delete()
				print 'Key %s successfully deleted' % key_name
			bucket.delete()
			print 'Bucket %s successfully deleted' % bucket_name
		except Exception, e:
			print 'Could not delete bucket %s' % bucket_name
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
			print 'Could not delete bucket %s' % args[1]
			sys.exit(1)
	else:
		print USAGE_MESSAGE
	sys.exit(2)