"""Upload the contents of your Downloads folder to Dropbox.

This is an example app for API v2.
"""

# from __future__ import print_function

# import argparse
# import contextlib
# import datetime
# import os
# import six
# import sys
# import time
# import unicodedata

# if sys.version.startswith('2'):
#     input = raw_input  # noqa: E501,F821; pylint: disable=redefined-builtin,undefined-variable,useless-suppression

import dropbox, sys, os, re
import shutil

APP_KEY= "brdyyqjcmyb1fib"
APP_SECRET= "bckefpjgtz7e9au"
TOKEN= "FJcbNXHb_ckAAAAAAAC1_tsy6EMXzNU93vIxoGwmEUNkAal3L2QYMUccxMW0v1N0"


BASE_DIR= "/mnt/c/Users/Andrea/GIT/DropboxSyncScript/"
UPLOAD_DIR= BASE_DIR+"upload/"
ARCHIVE_DIR= BASE_DIR+"archive/"


class TransferData:
    def __init__(self, access_token):
        self.access_token = access_token

    def upload_file(self, file_from, file_to):
        """upload a file to Dropbox using API v2 """
        dbx = dropbox.Dropbox(self.access_token)
        try:
            dbx.users_get_current_account()
        except AuthError:
            sys.exit("ERROR: Invalid access token; try re-generating an "
                "access token from the app console on the web.")

        with open(file_from, 'rb') as f:
            dbx.files_upload(f.read(), file_to)


def initDropBox():
    if (len(TOKEN) == 0):
        sys.exit("ERROR: Looks like you didn't add your access token. "
            "Open up backup-and-restore-example.py in a text editor and "
            "paste in your token in line 14.")
    return dropbox.Dropbox(TOKEN)


def moveFile (filename, source, dest):
    theFile= fullName (filename, source)
    if not os.path.isfile(theFile): return
    if not os.path.isdir(source): return
    if not os.path.isdir(dest): return
    shutil.move(theFile, fullName (filename, dest))


def fullName (filename, dir):
    return os.path.join (dir, filename)


def extractFileInfo (filename):
    filename= filename.upper()
    validFile= re.findall (r"^.*_[0-9]*_[0-9]*_(INDIVIDUAL|TEAM)\.(ZIP|XLS)", filename)
    if len(validFile) == 0: return None
    parts= filename.split("_")
    # combin in a list. remove extension from the last
    return [ parts[0], parts[1], parts[2], os.path.splitext(parts[3])[0] ] 


def fileInfoToPath (fileInfo):
    if fileInfo[-1] == "INDIVIDUAL":
        return os.path.join (fileInfo[0], fileInfo[1], fileInfo[2])
    else:
        return os.path.join (fileInfo[0], "TEAMS", fileInfo[1], fileInfo[2])


if __name__ == '__main__':
    
    # connect to Dropbox
    dbx= TransferData(TOKEN)
   
    # check files
    print "Check though files"  
    for filename in os.listdir(UPLOAD_DIR):
        fileInfo = extractFileInfo (filename)
        if fileInfo != None:
            destFile = os.path.join ("/", fileInfoToPath(fileInfo), filename)

            print "Uploading ", fullName(filename, UPLOAD_DIR), " to ", destFile
            dbx.upload_file (fullName(filename, UPLOAD_DIR), destFile)
            moveFile (filename, UPLOAD_DIR, ARCHIVE_DIR)