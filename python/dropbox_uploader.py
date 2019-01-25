#!/usr/bin/python


import dropbox
from ConfigParser import SafeConfigParser
import shutil, sys, os, re
import datetime

# GLOBALS loaded from config files
parser = SafeConfigParser()
parser.read('../config.ini')

TOKEN = parser.get('DEFAULT','TOKEN')
BASE_DIR= parser.get('DEFAULT','BASE_DIR')
UPLOAD_DIR= BASE_DIR + parser.get('DEFAULT','UPLOAD_DIR')
ARCHIVE_DIR= BASE_DIR + parser.get('DEFAULT','ARCHIVE_DIR')


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

def renameFile (filename, newFilename):
    if not os.path.isfile(filename): return
    os.rename(filename, newFilename)

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
        return os.path.join (fileInfo[0], "HW"+fileInfo[1], fileInfo[2])
    else:
        return os.path.join (fileInfo[0], "HW"+fileInfo[1], "TEAMS", fileInfo[2])



# --------   MAIN    -------------- 


if __name__ == '__main__':
    
    # connect to Dropbox
    dbx= TransferData(TOKEN)
   
    # check files
    print "Check though files"  
    for filename in os.listdir(UPLOAD_DIR):
        fileInfo = extractFileInfo (filename)
        if fileInfo != None:
            destFile = os.path.join ("/", fileInfoToPath(fileInfo), filename)

            srcOriginal= fullName(filename, UPLOAD_DIR)
            newFilename= str(datetime.datetime.now())+"_"+filename
            srcModified= fullName(newFilename, UPLOAD_DIR)

            print "Uploading ", srcOriginal, " to ", destFile
            dbx.upload_file (srcOriginal, destFile)
            renameFile (srcOriginal, srcModified)
            moveFile (newFilename, UPLOAD_DIR, ARCHIVE_DIR)