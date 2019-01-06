#!/usr/bin/env python
# -*- coding: utf-8 -*-
import os, sys
import time
import datetime
import smtplib
import httplib
import json
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
import base64
reload(sys)
sys.setdefaultencoding('utf-8')

class ExchangeSender:

    def __init__(self):
        self.exchange_server="172.24.22.157"
        self.sender_name = ""
        self.receiver_name = []

    def compose(self, sender, receiver, subject, body):
        self.msg = MIMEMultipart()
        self.msg['From'] = sys.argv[1] + '<zhiban@futu5.com>'
        self.sender_name = sender
        self.msg['To'] = sys.argv[3]
        # self.msg['Cc'] = sys.argv[3]
        self.receiver_name = receiver
        self.msg['Subject'] = subject
        if body.find("<html>") != -1:
            self.msg.attach(MIMEText(u"%s" % body,'html', 'utf-8'))
        else:
            self.msg.attach(MIMEText(u"%s" % body,'plain', 'utf-8'))

    def send(self):
        print("sender " + self.sender_name)
        print("receiver " + self.receiver_name)
        print("content " + self.msg.as_string())
        self.server = smtplib.SMTP(self.exchange_server)
        self.server.set_debuglevel(1)
        self.server.sendmail(self.sender_name, self.receiver_name, self.msg.as_string())
        self.server.quit()
if __name__ == "__main__":
    sender = ExchangeSender()

    # url = 'http://api.oa.com/public/expandMailGroup?mailGroup=' + sys.argv[2] + '@futunn.com&nestStrategy=expand'
    # conn = httplib.HTTPConnection("api.oa.com")
    # conn.request(method="GET",url=url)
    # info = json.loads(conn.getresponse().read())
    # receiver = info['data']
    receiver = sys.argv[2]
    # s = sys.argv[5]
    # if os.path.exists(s):
    #      f = open(s,'r')
    #      content = f.read()
    #      f.close()
    content = sys.argv[5]
    sender.compose("zhiban@futu5.com", receiver, sys.argv[4], content)
    localtime = time.asctime( time.localtime(time.time()) )
    print("=====================================================================================================\r")
    print("=============================发送时间为 :" + localtime + "====================================\r")
    print("=====================================================================================================\r")
    sender.send()
    print("send\r")
