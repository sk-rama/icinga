#!/usr/bin/env python

import argparse
import subprocess
import datetime
import sys

#SSL_CERT OK - X.509 certificate for '*.secar.cz' from 'Thawte SSL CA' valid until Dec 28 23:59:59 2014 GMT (expires in 179 days)|days=179;30;29;;

return_code_0 = 0       #OK             UP
return_code_1 = 1       #WARNING        UP or DOWN/UNREACHABLE
return_code_2 = 2       #CRITICAL       DOWN/UNREACHABLE
return_code_3 = 3       #UNKNOWN        DOWN/UNREACHABLE


parser = argparse.ArgumentParser()
parser.add_argument("-D", action="store", dest="domain_name", default='secar.cz', type=str, help="Domain name to check")
parser.add_argument("-w", action="store", dest="warning", type=int, default=60, help="Response time to result in warning status (days) - default 60 days")
parser.add_argument("-c", action="store", dest="critical", type=int, default=30, help="Response time to result in critical status (days) - default 30 days")
parser.add_argument('--version', action='version', version='%(prog)s 0.1')
parser.parse_args()


def return_date_from_whois_cz(domena_cz):
    try:
        command_whois_cz = 'whois ' + domena_cz + ' | grep "expire:" | awk -F"expire:" \'{print $2}\' '
        vysledok = subprocess.check_output(command_whois_cz, shell=True)
        vysledok = str(vysledok)        #konvertujeme vysledok do stringu
        vysledok = vysledok.strip()     #odstranime vsetky biele znaky
        return vysledok
    except:
        print "ERROR: domain is probably not in whois database or expire date not found"
        sys.exit(return_code_3)

def return_date_from_whois_sk(domena_sk):
    try:
        command_whois_sk = 'whois ' + domena_sk + ' | grep "Valid-date" | awk -F"Valid-date" \'{print $2}\' '
        vysledok = subprocess.check_output(command_whois_sk, shell=True)
        vysledok = str(vysledok)        #konvertujeme vysledok do stringu
        vysledok = vysledok.strip()     #odstranime vsetky biele znaky
        return vysledok
    except:
        print "ERROR: domain is probably not in whois database or expire date not found"
        sys.exit(return_code_3)

def return_date_from_whois_org(domena):
    try:
        command_whois = 'whois ' + domena + ' | grep "Registry Expiry Date:"| awk -F"Date:" \'{print $2}\' | cut -f 1 '
        vysledok = subprocess.check_output(command_whois, shell=True)
        vysledok = str(vysledok)        #konvertujeme vysledok do stringu
        vysledok = vysledok.strip()     #odstranime vsetky biele znaky
        if ('\n') in vysledok:          #zistime ci vo vysledku existuje dalsi riadok
            index_new_line = vysledok.index('\n')
            vysledok = vysledok[:index_new_line]
            return vysledok
        else:
            return vysledok
    except:
        print "ERROR: domain is probably not in whois database or expire date not found"
        sys.exit(return_code_3)

def return_date_from_whois_generic(domena):
    try:
        command_whois = 'whois ' + domena + ' | grep "Expiration Date:"| awk -F"Date:" \'{print $2}\' | cut -f 1 '
        vysledok = subprocess.check_output(command_whois, shell=True)
        vysledok = str(vysledok)        #konvertujeme vysledok do stringu
        vysledok = vysledok.strip()     #odstranime vsetky biele znaky
        if ('\n') in vysledok:          #zistime ci vo vysledku existuje dalsi riadok
            index_new_line = vysledok.index('\n')
            vysledok = vysledok[:index_new_line]
            return vysledok
        else:
            return vysledok
    except:
        print "ERROR: domain is probably not in whois database or expire date not found"
        sys.exit(return_code_3)

def days_to_expire_domain_cz(date_in_format_D_M_Y):     #vrati pocet dni do expiracie-datum expiracie je vo formate napr. 29.10.2018
    expire_date = datetime.datetime.strptime(date_in_format_D_M_Y, "%d.%m.%Y")
    today_date = datetime.datetime.now()
    return str(( expire_date - today_date ).days) 

def days_to_expire_domain_sk(date_in_format_Y_M_D):     #vrati pocet dni do expiracie-datum expiracie je vo formate napr. 2015-06-23
    expire_date = datetime.datetime.strptime(date_in_format_Y_M_D, "%Y-%m-%d")
    today_date = datetime.datetime.now()
    return str(( expire_date - today_date ).days)

def days_to_expire_domain_generic(datum):
    command01 = 'date +%s --date="{}"'.format(datum)
    expire_seconds = subprocess.check_output(command01, shell=True)
    expire_seconds = expire_seconds.strip()
    command02 = 'date +%s'
    today_seconds = subprocess.check_output(command02, shell=True)
    today_seconds = today_seconds.strip()
    return ( int(expire_seconds) - int(today_seconds) ) / 86400
    


def return_code(expiry_days):
    hodnota_warning = results.warning
    hodnota_critical = results.critical
    if int(expiry_days) > hodnota_warning:
        return return_code_0
    elif int(expiry_days) <= hodnota_critical:
        return return_code_2
    elif int(expiry_days) <= hodnota_warning:
        return return_code_1
    else:
        return return_code_3


def return_date_for_domain(domain):
    index_dot_in_domain_name = domain.index('.') + 1    #zistime na ktorej pozicii sa v domenovom nazve nachadza bodka a zvysime index o 1 
    domain_suffix = domain[index_dot_in_domain_name:]   #ziskame koncovku z domeny
    if domain_suffix == 'cz':
        expire_date = return_date_from_whois_cz(domain)
        return [expire_date, days_to_expire_domain_cz(expire_date)]                     #vraciame list, v ktorom je na pozicii 0 datum a na pozicii 1 pocet dni do expiracie
    elif domain_suffix == 'sk':
        expire_date = return_date_from_whois_sk(domain)
        return [expire_date, days_to_expire_domain_sk(expire_date)]                     #vraciame list, v ktorom je na pozicii 0 datum a na pozicii 1 pocet dni do expiracie
    elif ( domain_suffix == 'com' or domain_suffix == 'net' or domain_suffix == 'biz' or domain_suffix == 'us' or domain_suffix == 'mobi' ):
        expire_date = return_date_from_whois_generic(domain)
        return [expire_date, days_to_expire_domain_generic(expire_date)]                #vraciame list, v ktorom je na pozicii 0 datum a na pozicii 1 pocet dni do expiracie
    elif domain_suffix == 'org':
        expire_date = return_date_from_whois_org(domain)
        return [expire_date, days_to_expire_domain_generic(expire_date)]                #vraciame list, v ktorom je na pozicii 0 datum a na pozicii 1 pocet dni do expiracie
    else:
        print "domain name " + domain + " is not supported"
        sys.exit(return_code_3) 

def print_return_code(domain):
    try:
        expir_date = return_date_for_domain(domain)[0]
        expir_days = return_date_for_domain(domain)[1]
        expir_code = int(return_code(expir_days))
        if expir_code == return_code_0:
            print 'domain {0} is OK and is valid until {1} (expires in {2} days)|days={3};{4};{5};;'.format(domain, expir_date, expir_days, expir_days, results.warning, results.critical)
            return return_code_0
        elif expir_code == return_code_1:
            print 'domain {0} is WARNING and is valid until {1} (expires in {2} days)|days={3};{4};{5};;'.format(domain, expir_date, expir_days, expir_days, results.warning, results.critical)
            return return_code_1
        elif expir_code == return_code_2:
            print 'domain {0} is CRITICAL and is valid until {1} (expires in {2} days)|days={3};{4};{5};;'.format(domain, expir_date, expir_days, expir_days, results.warning, results.critical)
            return return_code_2
        else:
            return return_code_3
    except:
        print "Unknown Error"
        return return_code_3


    

    
    
   
results = parser.parse_args()
print_return_code(results.domain_name)
