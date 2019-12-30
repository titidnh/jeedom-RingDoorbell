#!/usr/bin/python3

from ring_doorbell import Ring
import sys, getopt

def main(argv):
   username = ''
   passwd = ''
   uid = ''
   opts, args = getopt.getopt(argv,"u:p:i:")
   for opt, arg in opts:
      if opt in ("-u"):
         username = arg
      elif opt in ("-p"):
         passwd = arg
      elif opt in ("-i"):
         uid = arg
   myring = Ring(username, passwd)
   if myring.is_connected == True:
       for doorbell in list(myring.doorbells):
             if doorbell.id == uid:
                doorbell.update()
                for event in doorbell.history(kind='ding',limit=15):
                   print(str(event['id'])+'||'+str(event['answered'])+'||'+str(event['created_at']))

if __name__ == "__main__":
   main(sys.argv[1:])
