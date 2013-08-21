import MySQLdb as mdb

class Hadoop:
    def connect(self):
        self.con = mdb.connect('localhost', 'root',  'tatishev5.4', 'nlp_systems')
        self.cur = self.con.cursor()
    def close(self):
        self.con.close()
    def add_record(self, text):
        with self.con:
            self.cur.execute("""INSERT INTO hadoop (log)
                    VALUES (%s)""",
                    (
                        text
                    ) )

