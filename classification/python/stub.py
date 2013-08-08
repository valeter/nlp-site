#!/usr/bin/python
# -*- coding: utf-8 -*-

import os
from os import listdir
from os.path import isfile, join, dirname, abspath
import time
import boto
import subprocess
import sys
import time
from boto.emr.connection import EmrConnection
from boto.emr.step import JarStep
from boto.emr.bootstrap_action import BootstrapAction
from boto.s3.key import Key

def get_result():
	input_path = dirname(abspath(__file__)) + "/../file-upload/server/php/files/"
	input_files = [ f for f in listdir(input_path) if isfile(join(input_path,f)) ]
	for input_file in input_files:
		if (".nlpresult" in str(input_file) or str(input_file).startswith('.')):
			continue
		result_filename = dirname(abspath(__file__)) + "/../file-upload/server/php/files/" + input_file + ".nlpresult"
		with open(result_filename, 'w') as f:
			f.write("UDK: 051\n")
			f.write("GRNTI: 211\n")

if __name__ == "__main__":
    get_result()