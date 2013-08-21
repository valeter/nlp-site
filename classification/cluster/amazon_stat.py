import MySQLdb as mdb
from datetime import datetime
import sys
import os

class AmazonStats:
    def connect(self):
        self.con = mdb.connect('localhost', 'root',  'tatishev5.4', 'nlp_systems')
        self.cur = self.con.cursor()
    def close(self):
        self.con.close()
    def add_record(self, node, nodenum, time, payed_on=None):
        with self.con:
            if payed_on is None:
                payed_on = datetime.now()
            try:
                self.cur.execute("""INSERT INTO amazon (date, node, nodenum, time)
                        VALUES (%s, %s, %s, %s)""",
                        (
                            payed_on,
                            node,
                            nodenum,
                            time / 60
                        ) )
            except Exception, e:
                pass