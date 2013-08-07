#!/usr/bin/python

import boto

USAGE_MESSAGE = 'Usage: ./write_folder_to_aws.py <folder name> <bucket> <create new bucket: true/false>'

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