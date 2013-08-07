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
	time.sleep(600)

if __name__ == "__main__":
    get_result()