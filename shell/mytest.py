#!/usr/bin/env python
# -*- coding: utf-8 -*-
import os, sys
import time
import datetime
import smtplib
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
import base64
reload(sys)
sys.setdefaultencoding('utf-8')

class ExchangeSender:

    def __init__(self):
        self.exchange_server="172.24.22.171"
        self.sender_name = ""
        self.receiver_name = []

    def compose(self, sender, receiver, subject, body):
        self.msg = MIMEMultipart()
        self.msg['From'] = sender
        self.sender_name = sender
        self.msg['To'] = " ,".join(receiver)
        self.receiver_name = receiver
        self.msg['Subject'] = subject
        if body.find("<html>") != -1:
            self.msg.attach(MIMEText(u"%s" % body,'html', 'utf-8'))
        else:
            self.msg.attach(MIMEText(u"%s" % body,'plain', 'utf-8'))

    def send(self):
        self.server = smtplib.SMTP(self.exchange_server)
        self.server.set_debuglevel(1)
        self.server.sendmail(self.sender_name, self.receiver_name, self.msg.as_string())
        self.server.quit()

if __name__ == "__main__":
    sender = ExchangeSender()

    path = "/data/web_publish/monitor_duty_system_storage/mail_file" #文件夹目录
    # path = "C:\Python27\mail" #文件夹目录  
    files= os.listdir(path) #得到文件夹下的所有文件名称  

    for file in files:
        f = open(path+"/"+file,'r')
        content = f.read() 
        f.close()
        os.remove(path+"/"+file)
        receiver = ["aiden@futunn.com","payton@futunn.com"]
        sender.compose("aiden@futu5.com", receiver,'值班邮件', content)
        sender.send()
