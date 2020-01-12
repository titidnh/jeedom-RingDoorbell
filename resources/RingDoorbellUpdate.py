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
   for doorbell in list(myring.doorbells):
        doorbell.update()
        for event in doorbell.history(limit=10):
           print(str(doorbell.id)+'||'+str(event['id'])+'||'+str(event['kind'])+'||'+str(event['answered'])+'||'+str(event['created_at']))

if __name__ == "__main__":
   main(sys.argv[1:])
