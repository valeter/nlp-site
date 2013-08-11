#!/usr/bin/python
# -*- coding: utf-8 -*-

import sys
from boto.emr.connection import EmrConnection

def terminate(cluster_id):
	try:
		emr_connection = EmrConnection()
		emr_connection.set_termination_protection(cluster_id, False)
		emr_connection.terminate_jobflow(cluster_id)
		return True
	except Exception, e:
		print e
		return False

def main(args):
	script_name, cluster_id = args
	if terminate(cluster_id):
		print "true"
	else:
		print "false"

if __name__ == "__main__":
	args = sys.argv
	main(args)
	sys.exit()