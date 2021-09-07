#!/usr/bin/python3

import argparse
import datetime
import time
import signal
import sys
from functools import partial

# cd /etc/rc-local/python-packages
# git clone https://github.com/richardpenman/whois.git
# cp whois package from whois to ../whois
sys.path.insert(0,'/etc/rc-local/python_packages')
import whois

__version__ = '1.0'

return_code = { 0: [0, "OK"],
                1: [1, "WARNING"],
                2: [2, "CRITICAL"],
                3: [3, "UNKNOWN"]
              }


def plugin_arguments():
    parser = argparse.ArgumentParser()
    parser.add_argument("-D", action="store", dest="domain_name", default='secar.cz', type=str, help="Domain name to check")
    parser.add_argument("-w", action="store", dest="warning", type=int, default=60, help="Response time to result in warning status (days) - default 60 days")
    parser.add_argument("-c", action="store", dest="critical", type=int, default=30, help="Response time to result in critical status (days) - default 30 days")
    parser.add_argument("-t", "--timeout", help="Timeout in seconds (default 5s)", type=int, default=5)
    parser.add_argument('-V', '--version', action='version', version='%(prog)s v' + sys.modules[__name__].__version__)
    return parser.parse_args()


def handle_sigalrm(signum, frame, timeout=None):
    print(f'Plugin timed out after {timeout} seconds')
    sys.exit(3)


def plugin_output(domain, plugin_exit, date_string, days, warning, critical):
    if plugin_exit != 3:
        print(f"{return_code[plugin_exit][1]}: domain {domain} is {return_code[plugin_exit][1]} and is valid until {date_string} (expires in {days} days)|'days'={days}d;{warning};{critical};;")
        sys.exit(return_code[plugin_exit][0])
    else:
        print(f'UNKNOWN: domain {domain} is UNKNOWN in whois database')
        sys.exit(3)


def days_to_expire(days: datetime):
    if days is not None:
        today = datetime.datetime.today()
        diff  = days - today
        return diff.days
    else:
        print(f'UNKNOWN: domain has not set expiration date')
        sys.exit(3)


def return_exit_state(days, warning, critical):
    if int(days) > warning:
        return return_code[0][0]
    elif int(days) <= critical:
        return return_code[2][0]
    elif int(days) <= warning:
        return return_code[1][0]
    else:
        return return_code[3][0]


def plugin_output(domain, plugin_exit, date_string, days, warning, critical):
    if plugin_exit != 3:
        print(f"{return_code[plugin_exit][1]}: domain {domain} is {return_code[plugin_exit][1]} and is valid until {date_string} (expires in {days} days)|'days'={days};{warning};{critical}")
        sys.exit(return_code[plugin_exit][0])
    else:
        print(f'UNKNOWN: domain {domain} is UNKNOWN in whois database')
        sys.exit(3)


if __name__ == "__main__":
    args = plugin_arguments()

    signal.signal(signal.SIGALRM, partial(handle_sigalrm, timeout=args.timeout))
    signal.alarm(args.timeout)

    # time.sleep(10)    # test of plugin timeout

    try:
        domain = whois.whois(args.domain_name)
    except:
        print(f'UNKNOWN: domain {args.domain_name} is UNKNOWN in whois database')
        sys.exit(3)


    expire_date = domain.expiration_date
    days_to_exp = days_to_expire(expire_date)
    expire_code = int(return_exit_state(days_to_exp, args.warning, args.critical))
   
    plugin_output(args.domain_name, expire_code, domain.expiration_date.date(), days_to_exp, args.warning, args.critical)
