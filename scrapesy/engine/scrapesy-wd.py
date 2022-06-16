#!/usr/bin/env python3

# scrapesy-wd.py
# This Python script is a deamon that will continuously monitor the '/opt/scrapesy/combolists/' directory
# for new files dropped by 'scrapesy.py' When new files are discovered, this daemon will call
# the 'CredsToES.py' script to begin parsing these files for relevant credentials and send to
# the local Elasticsearch instance for review.

import os
import time
import logging
from watchdog.observers import Observer
from watchdog.events import PatternMatchingEventHandler

#Define logging parameters
logging.basicConfig(filename="/opt/scrapesy/logs/scrapesy-watchdog.log",
                    level=logging.DEBUG,
                    format='%(asctime)s - %(levelname)s: %(message)s',
                    datefmt='%Y-%m-%d %H:%M:%S')

def on_created(event):
    #Notify and log when a new file is detected; wait 180 seconds to allow the file to finish downloading via 'scrapesy.py'
    logging.info(f"New file {event.src_path} was found in {path} - waiting 10 seconds before checking file size")
    print(f"New file {event.src_path} was created - waiting 10 seconds before checking file size")
    time.sleep(10)

    #Check if file is empty; if so, remove and skip; else send to 'CredsToES.py'
    size = os.path.getsize(event.src_path)
    if size == 0:
        logging.error(f"{event.src_path} is an empty file - deleting and skipping")
        print(f"{event.src_path} is empty - deleting and skipping")
        os.system(f"rm -f {event.src_path}")
    else:
        logging.info(f"{event.src_path} is not an empty file - sending to 'CredsToES.py' for processing")
        print(f"{event.src_path} is not an empty file - sending to 'CredsToES.py' for processing")
        os.system(f"python3 /opt/scrapesy/CredsToES.py {event.src_path} localhost 9200 -i scrapesy-index")

if __name__ == "__main__":
    patterns = "*"
    ignore_patterns = ""
    ignore_directories = False
    case_sensitive = True

    my_event_handler = PatternMatchingEventHandler(patterns, ignore_patterns, ignore_directories, case_sensitive)
    my_event_handler.on_created = on_created

    path = "/opt/scrapesy/combolists/"
    go_recursively = True
    my_observer = Observer()
    my_observer.schedule(my_event_handler, path, recursive=go_recursively)

    my_observer.start()

    try:
        while True:
            time.sleep(1)
    except KeyboardInterrupt:
        my_observer.stop()
        my_observer.join()