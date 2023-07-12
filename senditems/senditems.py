# senditems.py - desktop script to read a file containing 
# Workflows' HTML output of items to be pulled, and POST
# that file to a website to ingest into the holds database.
# For Sedona Library "holds pulling".
# Mark Riordan  2023-07-07

import sys
import requests

def read_entire_file(filename):
    file = open(filename,mode='r')
    contents = file.read()
    file.close()
    return contents

def post_items(contents):
    url = 'https://60bits.net/sedlib/postallitems.php'
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
        else:
            print("Unrecognized response: " + responseBody)
    pass

def main():
    if len(sys.argv) != 2:
        print("Usage: python senditems.py WorkflowsHTMLItemsFilename")
    else:
        filename = sys.argv[1]
        contents = read_entire_file(filename)
        print(f"Read {len(contents)} characters from {filename}")
        post_items(contents)
    pass

main()
