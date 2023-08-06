# senditems.py - desktop script to read a file containing 
# Workflows' HTML output of items to be pulled, and POST
# that file to a website to ingest into the holds database.
# For Sedona Library "holds pulling".
# Mark Riordan  2023-07-07

import sys
import requests
from tkinter import *

def read_entire_file(filename):
    file = open(filename,mode='r')
    contents = file.read()
    file.close()
    return contents

def post_items(contents):
    url = 'https://scopehustler.net/zz/sedlib/postallitems.php'
    dataToPost = {'password': 'se', 'items': contents}

    # Send POST request with FORM data using the data parameter
    response = requests.post(url, data=dataToPost)
    responseBody = response.text
    if "Invalid password" in responseBody:
        print("Invalid password (internal error)")
    else:
        # Parse the response to get the number of items loaded.
        idx = responseBody.find("Loaded ")
        if idx >= 0:
            loaded = responseBody[idx:]
            idx = loaded.find("<")
            loaded = loaded[:idx-1]
            print(loaded)
            return loaded
        else:
            msg = "Unrecognized response: " + responseBody
            print(msg)
            return msg
    pass

def show_message(msg):
    # Create an instance of tkinter frame
    win=Tk()
    win.geometry("600x250")
    win.title('senditems')

    label = Label(win,text=msg,foreground='orange', font='Arial 14', 
                  wraplength=450, justify='left')
    label.pack(ipadx=10, ipady=10)

    win.mainloop()

def main():
    if len(sys.argv) != 2:
        print("Usage: python senditems.py WorkflowsHTMLItemsFilename")
    else:
        try:
            filename = sys.argv[1]
            contents = read_entire_file(filename)
            print(f"Read {len(contents)} characters from {filename}")
            msg = post_items(contents)
            show_message(msg)
        except Exception as err:
            print(f"Exception: {err}")
            show_message(err)
    pass

main()
