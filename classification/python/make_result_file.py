#!/usr/bin python
# -*- coding: utf-8 -*-

from os import listdir
from os.path import isfile, join, dirname, abspath
import time

def get_result():
	input_path = dirname(abspath(__file__)) + "/../file-upload/server/php/files/"

	input_files = [ f for f in listdir(input_path) if isfile(join(input_path,f)) ]
	print input_files
	for input_file in input_files:
		with open(input_path + str(input_file) + ".nlpresult", 'w') as output_file:
			output_file.write('015 Нормы юридического права')

if __name__ == "__main__":
    get_result()