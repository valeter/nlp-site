#!/usr/bin/python
# -*- coding: utf-8 -*-

import sys
from boto.emr.connection import EmrConnection

def get_cluster_status(cluster_id):
	try:
		emr_connection = EmrConnection()
		flow = emr_connection.describe_jobflow(cluster_id)
		if flow == None:
			return "none"
		return flow.state
	except Exception, e:
		return "none"

def main(args):
	script_name, cluster_id = args
	termination_statuses = [u'COMPLETED', u'FAILED', u'TERMINATED', u'SHUTTING_DOWN']
	cluster_status = get_cluster_status(cluster_id.strip())
	if cluster_status == "none" or cluster_status in termination_statuses:
		print "false"
		print cluster_status
	else:
		print "true"
		print cluster_status

if __name__ == "__main__":
	args = sys.argv
	main(args)
	sys.exit()