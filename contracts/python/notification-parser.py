#!/usr/bin/python
# -*- coding: utf-8 -*-

from xml.dom import minidom
from xml.dom.minidom import Node
from os.path import isfile, join, dirname, abspath


class Organization(object):
	name = "none" #String <notification>=><order>=><placerOrganization>=><fullName>
	ownership_type = "none" #String <notification>=><order>=><placerOrganization>=><organizationType>=><name>

	def __key(self):
		return '( ' + self.name + ' | ' + self.ownership_type + ' )'

	def __eq__(self, other):
		if isinstance(other, self.__class__):
			return self.__key() == other.__key()
		else:
			return false

	def __hash__(self):
		return hash(self.__key())

	def __str__(self):
		return unicode(self.__key())

	def __repr__(self):
		return self.__str__()

class Lot(object):
	number = "none" #String <notification>=><lots>=><lot>=><ordinalNumber>
	name = "none" #String <notification>=><lots>=><lot>=><subject>
	price = "none" #String <notification>=><lots>=><lot>=><maxPriceXml>
	winner = "none" #Organization
	funding = "none" #String <notification>=><lots>=><lot>=><customerRequirements>=><customerRequirement>=><financeSource>
	rubric1 = "none" #String <notification>=><lots>=><lot>=><products>=><code>
	rubric2 = "none"
	rubric3 = "none"

	def __key(self):
		return '( ' + self.name + ' | ' + self.number + ' | ' + self.price + ' | ' + unicode(self.winner) + ' | ' + unicode(self.funding) + ' | ' + unicode(self.rubric1) + ' | ' + unicode(self.rubric2) + ' | ' + unicode(self.rubric3) + ' )'

	def __eq__(self, other):
		if isinstance(other, self.__class__):
			return self.__key() == other.__key()
		else:
			return false

	def __hash__(self):
		return hash(self.__key())

	def __str__(self):
		return unicode(self.__key())

	def __repr__(self):
		return self.__str__()

class Contract(object):
	registryNumber = "none" #String <notification>=><notificationNumber>
	name = "none" #String <notification>=><orderName>
	region = "none" #String <notification>=><order>=><placerOrganization>=><factualAddress>=><region>=><fullName>
	lots = "none" #[Lot] <notification>=><lots>
	creationDate = "none" #String <notification>=><createDate>
	completionDate = "none" #String <notificationCommission>=><p3Date>
	client = "none" #Organization
	content = "none" #String

	def __key(self):
		ls = u'\n\r['
		for lot in self.lots:
			ls += unicode(lot) + u'\n\r'
		ls += u']'
		return '( ' + self.registryNumber + ' | ' + self.name + ' | ' + self.region + ' | ' + ls + ' | ' + self.creationDate + ' | ' + self.completionDate + ' | ' + unicode(self.client) + ' )'

	def __eq__(self, other):
		if isinstance(other, self.__class__):
			return self.__key() == other.__key()
		else:
			return false

	def __hash__(self):
		return hash(self.__key())

	def __str__(self):
		return unicode(self.__key())

	def __repr__(self):
		return unicode(self.__str__())

def getChildrenByTitle(node, child_name):
	result = []
	for child in node.childNodes:
		if child.localName == child_name:
			result.append(child)
	return result

def getChildByPath(node, path, delim):
	childs = path.split(delim)
	cur_node = node
	for child in childs:
		cur_nodes = getChildrenByTitle(cur_node, child)
		cur_node = cur_nodes[0]
	return cur_node.childNodes[0]

class Notification(object):
	#notif_filename #String
	#prot_filename #String

	def __init__(self, notif_fname, prot_fname):
		self.notif_filename = notif_fname
		self.prot_filename = prot_fname

	def parse(self): #Contract
		result = Contract()
		xmldoc = minidom.parse(self.notif_filename)
		notif_nodes = xmldoc.getElementsByTagName('notification')
		notif_node = notif_nodes[0]
		try:
			result.registryNumber = getChildByPath(notif_node, 'notificationNumber', ' ').data.strip().replace('\n', ' ')
		except Exception, e:
			result.registryNumber = "none"
			pass
		try:
			result.name = getChildByPath(notif_node, 'orderName', ' ').data.strip().replace('\n', ' ')
		except Exception, e:
			result.name = "none"
			pass
		try:
			result.region = getChildByPath(notif_node, 
				'order>=><placerOrganization>=><factualAddress>=><region>=><fullName',
				'>=><').data.strip().replace('\n', ' ')	
		except Exception, e:
			result.region = "none"
			pass
		try:
			result.client = Organization()
			result.client.name = getChildByPath(notif_node, 
				'order>=><placerOrganization>=><fullName',
				'>=><').data.strip().replace('\n', ' ')
			result.client.ownership_type = getChildByPath(notif_node, 
				'order>=><placerOrganization>=><organizationType>=><name',
				'>=><').data.strip().replace('\n', ' ')
		except Exception, e:
			result.client = "none"
			pass
		try:
			result.creationDate = getChildByPath(notif_node,
				'createDate',
				' ').data.strip().replace('\n', ' ')
		except Exception, e:
			result.creationDate = "none"
			pass
		try:
			result.completionDate = getChildByPath(notif_node,
				'notificationCommission>=><p3Date',
				'>=><').data.strip().replace('\n', ' ')
		except Exception, e:
			try:
				result.completionDate = getChildByPath(notif_node,
					'notificationCommission>=><p2Date',
					'>=><').data.strip().replace('\n', ' ')
			except Exception, e:
				try:
					result.completionDate = getChildByPath(notif_node,
						'notificationCommission>=><p1Date',
						'>=><').data.strip().replace('\n', ' ')
				except Exception, e:
					result.completionDate = "none"
					pass

		try:
			result.lots = []
			lots_nodes = getChildrenByTitle(notif_node, 'lots')
			
			lots_node = lots_nodes[0]
			lot_nodes = getChildrenByTitle(lots_node, 'lot')
			
			rubr_mapper = RubricsMapper(dirname(abspath(__file__)) + '/rubric_mapping.txt')
			for lot_node in lot_nodes:
				try:
					lot = Lot()
					try:
						lot.number = getChildByPath(lot_node,
							'ordinalNumber',
							' ').data.strip().replace('\n', ' ')
					except Exception, e:
						pass
					try:
						lot.name = getChildByPath(lot_node,
							'subject',
							' ').data.strip().replace('\n', ' ')
					except Exception, e:
						pass
					try:
						lot.price = getChildByPath(lot_node,
							'maxPriceXml',
							' ').data.strip().replace('\n', ' ')
					except Exception, e:
						try:
							lot.price = getChildByPath(lot_node,
								'maxPrice',
								' ').data.strip().replace('\n', ' ')
						except Exception, e:
							pass

					try:
						reqs_parent = getChildrenByTitle(lot_node, 'customerRequirements')
						reqs = getChildrenByTitle(reqs_parent[0], 'customerRequirement')
						for req in reqs:
							try:
								lot.funding = getChildByPath(req,
									'financeSource',
									' ').data.strip().replace('\n', ' ')
							except Exception, e:
								pass
					except Exception, e:
						pass

					try:
						lot_code = getChildByPath(lot_node,
							'products>=><product>=><code',
							'>=><').data.strip().replace('\n', ' ')
					except Exception, e:
						pass	
					try:
						lot.rubric1 = rubr_mapper.get_rubric1(lot_code)
						lot.rubric2 = rubr_mapper.get_rubric2(lot_code)
						lot.rubric3 = rubr_mapper.get_rubric3(lot_code)
					except Exception, e:
						pass

					result.lots.append(lot)
				except Exception, e:
					pass
		except Exception, e:
			result.lots = "none"
			pass

		try:
			winner_parser = WinnerParser(self.prot_filename)
			need2 = True
			for lot_node in result.lots:
				try:
					lot_node.winner = winner_parser.get_winner(lot_node.number)
					if lot_node.winner == None:
						lot_node.winner = "none"
					else:
						need2 = False
				except Exception, e:
					lot_node.winner = "none"
			if need2:
				raise Exception()
		except Exception, e:
			try:
				winner_parser = WinnerParser2(self.prot_filename)
				for lot_node in result.lots:
					try:
						lot_node.winner = winner_parser.get_winner(lot_node.number)
						if lot_node.winner == None:
							lot_node.winner = "none"
					except Exception, e:
						lot_node.winner = "none"
						pass
			except Exception, e:
				pass
		
		result.content = None
		return result

class WinnerParser(object):
	#prot_filename #String
	#lot_winners #{Integer:String}

	def __init__(self, prot_fname):
		self.prot_filename = prot_fname
		self.initialize_winners()

	def initialize_winners(self):
		self.lot_winners = {}
		prot_content = ""

		with open(self.prot_filename, 'r') as f:
			prot_content = f.read() 
		prot_content = unicode(prot_content, 'utf8')

		lot_num = 0
		while True:
			lot_num += 1
			lot_str = u"Лот №" + unicode(str(lot_num).strip()) + u"<"

			lot_ind = prot_content.find(lot_str, 0)
			if lot_ind == -1:
				break

			w_str = u"Победитель"
			w_ind = prot_content.find(w_str, lot_ind)

			if w_ind == -1:
				continue

			next_lot_num = lot_num + 1
			next_lot_str = u"Лот №" + unicode(str(next_lot_num).strip()) + u"<"
			next_lot_ind = prot_content.find(next_lot_str, 0)
			
			if next_lot_ind != -1:
				if next_lot_ind < w_ind:
					continue
			
			span_str = u"<span"
			span_ind = prot_content.find(span_str, w_ind)

			if span_ind == -1:
				continue

			span_ind = prot_content.find(span_str, span_ind + 1)

			if span_ind == -1:
				continue

			ob_str = u">"
			ob_ind = prot_content.find(ob_str, span_ind)

			if ob_ind == -1:
				continue

			start_ind = ob_ind + 1

			if start_ind >= len(prot_content):
				continue

			end_str = u"<"
			end_ind = prot_content.find(end_str, start_ind)

			if end_ind == -1:
				continue

			winner_str = prot_content[start_ind:end_ind]
			winner_str = winner_str.replace("&laquo;", "\"")
			winner_str = winner_str.replace("&raquo;", "\"")
			winner_str = winner_str.replace("&quot;", "\'")

			self.lot_winners[unicode(str(lot_num))] = winner_str

	def get_winner(self, number):
		return self.lot_winners[number]

class WinnerParser2(object):
	#prot_filename #String
	#lot_winners #{Integer:String}

	def __init__(self, prot_fname):
		self.prot_filename = prot_fname
		self.initialize_winners()

	def initialize_winners(self):
		self.lot_winners = {}
		prot_content = ""

		with open(self.prot_filename, 'r') as f:
			prot_content = f.read() 
		prot_content = unicode(prot_content, 'utf8')

		w_str = u">Результат оценки котировочных заявок<"
		w_ind = prot_content.find(w_str, 0)
		if w_ind == -1:
			return

		winner_str = "none"

		while (True):
			tr_str = u"<tr"
			tr_ind = prot_content.find(tr_str, w_ind)
			if tr_ind == -1:
				return

			span_str = u"<span"
			span_ind = prot_content.find(span_str, tr_ind)
			if span_ind == -1:
				return

			span_ind = prot_content.find(span_str, span_ind + 2)
			if span_ind == -1:
				return

			ob_str = u">"
			ob_ind = prot_content.find(ob_str, span_ind)
			if ob_ind == -1:
				return

			start_ind = ob_ind + 1
			if start_ind >= len(prot_content):
				return

			end_str = u"<"
			end_ind = prot_content.find(end_str, start_ind)
			if end_ind == -1:
				return

			pre_winner_str = prot_content[start_ind:end_ind]
			pre_winner_str = pre_winner_str.replace("&laquo;", "\"")
			pre_winner_str = pre_winner_str.replace("&raquo;", "\"")
			pre_winner_str = pre_winner_str.replace("&quot;", "\'")


			span_ind = prot_content.find(span_str, end_ind + 2)
			if span_ind == -1:
				return

			span_ind = prot_content.find(span_str, span_ind + 2)
			if span_ind == -1:
				return

			ob_str = u">"
			ob_ind = prot_content.find(ob_str, span_ind)
			if ob_ind == -1:
				return

			start_ind = ob_ind + 1
			if start_ind >= len(prot_content):
				return

			end_str = u"<"
			end_ind = prot_content.find(end_str, start_ind)
			if end_ind == -1:
				return

			pobeditel = prot_content[start_ind:end_ind].strip()
			if pobeditel == u"Победитель":
				winner_str = pre_winner_str
				break

			w_ind = tr_ind + 1

		self.lot_winners[unicode(str(1))] = winner_str

	def get_winner(self, number):
		return self.lot_winners[number]

class RubricsMapper(object):
	#rm_filename #String
	#rubruc1 #{}
	#rubruc2 #{}
	#rubruc3 #{}	

	def __init__(self, rm_fname):
		self.rm_filename = rm_fname
		self.initialize_rubrics()

	def initialize_rubrics(self):
		self.rubric1 = {}
		self.rubric2 = {}
		self.rubric3 = {}
		try:
			with open(self.rm_filename, 'r') as f:
				while True:
					codes = []
					fin = False
					while True:
						line = f.readline().strip()
						if line == "===":
							break
						if len(line) == 0:
							fin = True
							break
						codes.append(line[1:line.find(":")])

					if fin:
						break

					rubric1 = unicode(f.readline().strip(), 'cp1251')
					rubric2 = unicode(f.readline().strip(), 'cp1251')
					rubric3 = unicode(f.readline().strip(), 'cp1251')
					f.readline()
					for code in codes:
						self.rubric1[code] = rubric1
						self.rubric2[code] = rubric2
						self.rubric3[code] = rubric3
		except Exception, e:
			pass

	def get_rubric1(self, str):
		return self.rubric1[str]

	def get_rubric2(self, str):
		return self.rubric2[str]

	def get_rubric3(self, str):
		return self.rubric3[str]

notification = Notification('/home/valter/Dropbox/contracts/printForm4.xml', '/home/valter/Dropbox/contracts/Сведения заказа4.html')
print unicode(notification.parse())