-- Abandoned Lua script to post a Sedona Library list of 
-- held items to my holds application.
-- I instead wrote senditems.py to accomplish this.
-- Mark Riordan  2023-07-07

function readAll(filename)
    local f = assert(io.open(filename, "rb"))
    local data = f:read("*all")
    f:close()
    return data
end

function postForm(itemsHTML)
    local http = require "socket.http"

    http.request("http://localhost/sedlib/postallitems.php",body)
end

function main()
    filename = arg[1]
    if filename == nil then
        print("Usage: lua senditems.lua WorkflowOutputFilename")
    else
        itemsHTML = readAll(filename)
        print(string.len(itemsHTML) .. " characters read from " .. filename)
        postForm(itemsHTML)
    end
end

main()
