#!/usr/bin/python3

from ring_doorbell import Ring,Auth
import sys, getopt

def main(argv):
   username = ''
   passwd = ''
   token_updated = None
   opts, args = getopt.getopt(argv,"u:p:")
   for opt, arg in opts:
      if opt in ("-u"):
         username = arg
      elif opt in ("-p"):
         passwd = arg
   auth = Auth(None, token_updated)
   auth.fetch_token(username, passwd)
   myring = Ring(auth)
   for dev in list(myring.doorbells):
      dev.update()
      print(dev.id+'||'+dev.family+'||'+dev.name)

if __name__ == "__main__":
   main(sys.argv[1:])
