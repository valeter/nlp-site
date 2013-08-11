import MySQLdb as mdb
from datetime import datetime

class Billing:
    def connect(self):
        self.con = mdb.connect('localhost', 'root',  'tatishev5.4', 'nlp_systems')
        self.cur = self.con.cursor()
    def close(self):
        self.con.close()
    def add_record(self, work_time_seconds, nodes, node_minute_price_cents, service, payed_on=None, payer_id=0):
        with self.con:
            if payed_on is None:
                payed_on = datetime.now()
            self.cur.execute("""INSERT INTO billing (payer_id, payed_on, service, amount_cents, work_time_seconds, nodes)
                    VALUES (%s, %s, %s, %s, %s, %s)""",
                    (
                        payer_id,
                        payed_on,
                        service,
                        node_minute_price_cents * nodes * work_time_seconds / 60,
                        work_time_seconds,
                        nodes
                    ) )

