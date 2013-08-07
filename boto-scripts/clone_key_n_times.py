#!/usr/bin/python

import boto
from boto.s3.key import Key
import sys
import os
from os import listdir
from os.path import isfile, join

USAGE_MESSAGE = 'Usage: ./clone_file_n_times.py <input bucket name> <input file> <output bucket name> <output file> <number of clones>'


def check_args(args):
	return len(args) == 6

def get_clone_name(key_name, clone_num):
	point_index = key_name.find('.')
	if (point_index == -1):
		return key_name + str(clone_num)
	result_list = list(key_name)
	result_list.insert(point_index, str(clone_num))
	result_name = ''.join(result_list)
	return result_name

def main(args):
	script_name, input_bucket_name, input_key_name, output_bucket_name, output_key_name, number_of_clones = args
	number_of_clones = int(number_of_clones)
	
	try:
		s3_connection = boto.connect_s3()
		print 'Connection to S3 service established'
		
		input_bucket = get_bucket(s3_connection, input_bucket_name)
		if (input_bucket == None):
			print "There's no bucket with name %s" % input_bucket_name
			return False
		print 'Input bucket was found'

		output_bucket = get_bucket(s3_connection, output_bucket_name)
		if (output_bucket == None):
			print "There's no bucket with name %s" % output_bucket_name
			return False
		print 'Output bucket was found'

		try:
			input_key = Key(input_bucket)	
			input_key.key = input_key_name
			if (number_of_clones > 1):
				for i in range(1, number_of_clones + 1):
					input_key.copy(output_bucket_name, get_clone_name(output_key_name, i))
			else:
				input_key.copy(output_bucket_name, output_key_name)
			print 'File successfully cloned %s times' % str(number_of_clones)
		except Exception, e:
			print 'Could not clone key %s' % input_key_name
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
			print 'Could not clone file %s in aws!' % args[2]
			sys.exit(1)
	else:
		print USAGE_MESSAGE
	sys.exit(2)