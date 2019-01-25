# DropboxSyncScript

## Overview
This software allows students to upload their homework on a form, and the instructor to receive the uploaded file in a Dropbox folder. The name of the file is formatted for easy retrieval, and it is stored in a folder following a precise tree sctructire (see image below).

This software is very vanilla and simple, but does the job. It does not provide any special security feature. Anyone can upload a file, and although a copy of each submitted file remains in an archive folder on the server, a submission with the same name overrides the any prior submission with the same name.

The software is free of use and can be modified. **Use it at your own risk**.

![alt text](docs/Overview.png)

## How to install

### Setup dependencies

Your server needs to be able to run Python (>2.7) and Php. You also need to install a couple of python modules for using **Dropbox** and **configparser**. Please refer to the [Dropbox](https://www.dropbox.com/developers/documentation/python) developers' docs. You might simply need to install your dependencies with pip, like this:

```
pip install configparser --user
pip install dropbox --user
```

or this:

```
pip install configparser
pip install dropbox
```
Finally, you need to setup an app with Dropbox and generate a *TOKEN* string - you'll need it later.

### Install

Close the repository on your server.

'''

'''




Modified first line of the python script
