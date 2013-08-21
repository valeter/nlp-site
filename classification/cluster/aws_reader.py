#!/usr/bin/python

import boto
import sys
from boto.s3.key import Key
import MySQLdb as mdb

def get_bucket(s3_connection, bucket_name):
	try:
		bucket = s3_connection.get_bucket(bucket_name)
		return bucket
	except Exception, e:
		print 'Could not access bucket %s' % bucket_name
		print e
		return None

class Reader(object):
	def __init__(self, bucket_name):
		self.bucket_name = bucket_name

	def read(self, filename):
		try:
			s3_connection = boto.connect_s3()
			bucket = get_bucket(s3_connection, self.bucket_name)
			if (bucket == None):
				return ""
			try:
				key = Key(bucket)
				key.key = filename
				return key.get_contents_as_string()
			except Exception, e:
				return ""
		except Exception, e:
			return ""