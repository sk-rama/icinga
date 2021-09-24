#!/usr/bin/env python3

# https://mibs.observium.org/mib/BROCADE-SYSTEM-MIB/#
# http://oid-info.com/get/1.3.6.1.4.1.1588.2.1.1.1.1.22.1.4

import argparse
import puresnmp  # pip3 install puresnmp
import signal
import sys
from functools import partial

return_code = { 0: [0, "OK"],
                1: [1, "WARNING"],
                2: [2, "CRITICAL"],
                3: [3, "UNKNOWN"]
              }

oid_swSensorTable = "1.3.6.1.4.1.1588.2.1.1.1.1.22"
oid_swDiagResult  = "1.3.6.1.4.1.1588.2.1.1.1.1.20"       # "The result of the power-on startup (POST) diagnostics."

swSensorType = { 
    "1": "temperature",
    "2": "fan",
    "3": "power-supply",
    "desc": "This object identifies the sensor type."
}

swSensorStatus = {
    "1": "unknown",
    "2": "faulty",
    "3": "below-min",
    "4": "nominal",
    "5": "above-max",
    "6": "absent",
    "desc": "The current status of the sensor."
}

swDiagResult = {
    "1": "sw-ok",
    "2": "sw-faulty",
    "3": "sw-embedded-port-fault"
}


swTable = {
    "0": "puresnmpIndex",
    "1": "swSensorIndex",
    "2": "swSensorType",
    "3": "swSensorStatus", 
    "4": "swSensorValue",
    "5": "swSensorInfo"
}




def handle_sigalrm(signum, frame, timeout=None):
    print(f'Plugin timed out after {timeout} seconds')
    sys.exit(3)


def plugin_arguments():
    parser = argparse.ArgumentParser()
    parser.add_argument("-H", "--host", dest="host", type=str, default="192.168.91.119", help="Hostname or IP address of the host to check") 
    parser.add_argument("-C", "--snmp_community", dest="community", type=str, default="public", help="SNMP Community (only with SNMP v1|v2c)")
    parser.add_argument("-v", "--snmp_version", dest="snmp_version", type=str, default="2c", help="SNMP Version to use [1, 2c or 3]") 
    parser.add_argument("-t", "--plugin_timeout", dest="timeout", type=int, default=10, help="Timeout in seconds for SNMP")
    parser.add_argument("-d", "--debug", dest="debug", default=False, help="Enable debugging for troubleshooting")
    return parser.parse_args()


def examine_sensor_type(dicts_in_list):
    result = []
    for d_ict in dicts_in_list:
        if d_ict['swSensorType'] == 1:
            d_ict['swSensorType'] = swSensorType["1"]
        elif d_ict['swSensorType'] == 2:
            d_ict['swSensorType'] = swSensorType["2"]
        elif d_ict['swSensorType'] == 3:
            d_ict['swSensorType'] = swSensorType["3"]
        else:
            d_ict['swSensorType'] = "sensor_type_unknown" 
        result.append(d_ict)
    return result      


def get_return_status(dicts_in_list):
    output        = ""
    return_status = 0
    for d_ict in dicts_in_list:
        if d_ict['swSensorStatus'] == 4:
            output = f'{output}{d_ict["swSensorInfo"].decode(encoding="utf-8").lstrip().rstrip()} is OK\n'
            return_status = max(return_status, 0)
        elif d_ict['swSensorStatus'] == 1:
            output = f'{output}{d_ict["swSensorInfo"].decode(encoding="utf-8").lstrip().rstrip()} is UNKNOWN\n'
            return_status = max(return_status, 2)
        elif d_ict['swSensorStatus'] == 2:
            output = f'{output}{d_ict["swSensorInfo"].decode(encoding="utf-8").lstrip().rstrip()} is FAULTY\n'
            return_status = max(return_status, 2)
        elif d_ict['swSensorStatus'] == 3:
            output = f'{output}{d_ict["swSensorInfo"].decode(encoding="utf-8").lstrip().rstrip()} is BELOW-MIN\n'
            return_status = max(return_status, 2)
        elif d_ict['swSensorStatus'] == 5:
            output = f'{output}{d_ict["swSensorInfo"].decode(encoding="utf-8").lstrip().rstrip()} is ABOVE-MAX\n'
            return_status = max(return_status, 2)
        elif d_ict['swSensorStatus'] == 6:
            output = f'{output}{d_ict["swSensorInfo"].decode(encoding="utf-8").lstrip().rstrip()} is ABSENT\n'
            return_status = max(return_status, 2)
        else:
            output = f'{output}{d_ict["swSensorInfo"].decode(encoding="utf-8").lstrip().rstrip()} is NOT in SNMP\n'
            return_status = max(return_status, 0)
    return[return_status, output.rstrip()]


def get_performance_data(dicts_in_list):
    output        = ""
    for d_ict in dicts_in_list:
        swSensorInfo = d_ict["swSensorInfo"].decode(encoding="utf-8").lstrip().rstrip()
        value        = d_ict["swSensorValue"]
        if d_ict["swSensorType"] == "temperature":
            unit = "temperature"
        elif d_ict["swSensorType"] == "fan":
             unit = "rpm"
        elif d_ict["swSensorType"] == "power-supply":
             unit = "power_state"
        else:
             continue
        output = f"{output}'{swSensorInfo}'={value} "
    return output
  
        
   
            




if __name__ == "__main__":
    args = plugin_arguments()

    signal.signal(signal.SIGALRM, partial(handle_sigalrm, timeout=args.timeout))
    signal.alarm(args.timeout)

    raw_table = puresnmp.table(args.host, args.community, oid_swSensorTable)

    new_table = [ {swTable[key]:d_ict[key] for key in d_ict } for d_ict in raw_table ]
    new_table = examine_sensor_type(new_table)

    return_state = get_return_status(new_table)
    perf_data = get_performance_data(new_table)

    if perf_data:
        print(f'{return_state[1]}|{perf_data}')
    else: 
        print(return_state[1])

    sys.exit(return_state[0])
