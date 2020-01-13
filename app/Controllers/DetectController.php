<?php
/** Red Framework Controller
 * Generated By Red Analytics
 *
 * Date: 06/01/2020
 * Time: 22:49:17
 * @author RedCoder
 */

namespace App\Controllers;


use Red\Base\Controller;
use Red\ValidateService\Validate;

class DetectController extends Controller
{

    private $results = array();

    /**
     * @var DatabaseController $this ->database_controller
     */
    private $database_controller;
    private $private_db_status;


    public function detect($hostname)
    {

        $hostname = str_replace("https://", "", $hostname);
        $hostname = str_replace("http://", "", $hostname);
        $hostname = str_replace("www.", "", $hostname);
        $hostname = trim($hostname, "/");

        echo PHP_EOL . "\e[1;37;40m    ";

        if (!Validate::validate($hostname, "required|method:domain|limit:1-63")) {
            echo "\e[1;31;40m[-] Wrong Hostname !\e[1;37;40m" . PHP_EOL;
            return false;
        }

        if (!$this->isConnected()) {
            echo "\e[1;31;40m[-] No Internet Connection !\e[1;37;40m" . PHP_EOL;
            return false;
        }

        if ($this->isConnected() && !$this->serverUp()) {
            echo "\e[1;31;40m[-] API Server is Down Temporary , Please Try Again Later .\e[1;37;40m" . PHP_EOL;
            return false;
        }

        $whois_handler = new WhoisController();
        $data = $whois_handler->fetchData($hostname);
        $organization = $whois_handler->getOrganization();

        if ($organization == "Cloudflare, Inc.") {
            $this->specificDetection($hostname, $data['query']);
        } else {
            $this->normalDetection($hostname, $data['query']);
        }

        return true;

    }

    public function normalDetection($hostname, $server_ip)
    {
        echo "\e[1;31;40m[*] Server IP Detected - No CloudFlare Protection" . PHP_EOL . PHP_EOL .

            "\t\e[1;31;40m[+] Hostname : \e[1;36;40m" . $hostname . PHP_EOL . PHP_EOL .

            "\t\e[1;31;40m[+] Server IP : \e[1;36;40m" . $server_ip . "\e[1;37;40m" . PHP_EOL;
    }

    public function specificDetection($hostname, $cloudflare_ip)
    {

        $this->database_controller = new DatabaseController();

        $this->private_db_status = $this->database_controller->DatabaseStatus();


        echo "\e[1;31;40m[!] CloudFlare IP : \e[1;36;40m" . $cloudflare_ip . "\e[1;37;40m" . PHP_EOL . PHP_EOL . "    ";

        sleep(1);

        echo "\e[1;31;40m[*] Trying to Detect Original IP : " . "\e[1;37;40m" . PHP_EOL . PHP_EOL . "    ";

        sleep(1);

        $continue = $this->method1($hostname);

        if ($continue == false) {
            return false;
        }

        sleep(1);

        $this->progressBar(20, 100);

        sleep(1);

        $continue = $this->method2($hostname);

        if ($continue == false) {
            return false;
        }

        sleep(1);

        $this->progressBar(40, 100);

        sleep(1);

        $continue = $this->method3($hostname);

        if ($continue == false) {
            return false;
        }

        sleep(1);

        $this->progressBar(60, 100);

        sleep(1);

        $this->method4($hostname);


        sleep(1);

        $this->progressBar(100, 100);

        sleep(1);

        if (count($this->results) > 0) {

            for ($counter = 0; $counter <= 4; $counter++) {
                if (!isset($this->results['method_1'])) {
                    $this->results['method_1'] = "Passed";
                } else if (!isset($this->results['method_2'])) {
                    $this->results['method_2'] = "Passed";
                } else if (!isset($this->results['method_3'])) {
                    $this->results['method_3'] = "Passed";
                } else if (!isset($this->results['method_4'])) {
                    $this->results['method_4'] = "Passed";
                }
            }

            echo PHP_EOL . "    \e[1;31;40m[*] Results" . PHP_EOL . PHP_EOL .

                "    \e[1;31;40m[*] Target : \e[1;36;40m" . $hostname . PHP_EOL . PHP_EOL .

                "\t\e[1;31;40m[+] Method 1 : \e[1;36;40m" . $this->results['method_1'] . PHP_EOL . PHP_EOL .

                "\t\e[1;31;40m[+] Method 2 : \e[1;36;40m" . $this->results['method_2'] . PHP_EOL . PHP_EOL .

                "\t\e[1;31;40m[+] Method 3 : \e[1;36;40m" . $this->results['method_3'] . PHP_EOL . PHP_EOL .

                "\t\e[1;31;40m[+] Method 4 : \e[1;36;40m" . $this->results['method_4'] . "\e[1;37;40m" . PHP_EOL;
        } else {
            echo PHP_EOL . "    \e[1;31;40m[!] Detection Failed" . PHP_EOL . PHP_EOL .

                "\t[-] OPs ! This Website is Well Protected\e[1;37;40m" . PHP_EOL;

            return false;
        }

        return true;

    }

    public function method1($hostname)
    {
        echo "\e[1;31;40m[*] Method 1 : \e[1;36;40mChecking Guardiran DNS History Database (" . number_format($this->private_db_status['ip_db']) . " Records) \e[1;37;40m" . PHP_EOL . PHP_EOL . "    ";

        sleep(1);

        $DNSHistory = new DNSHistoryController();
        $dns_history_method_result = $DNSHistory->DNSHistory($hostname);
        $dns_history_connection_status = $DNSHistory->getConnectionStatus();

        if ($dns_history_method_result != NULL && $dns_history_method_result != "false" && is_array($dns_history_method_result)) {
            echo "\e[1;31;40m[*] Result : " . PHP_EOL . PHP_EOL .

                "\t\e[1;31;40mDirect IP Found !" . PHP_EOL . PHP_EOL .

                "\t\e[1;31;40m[+] Original IP : \e[1;36;40m" . $dns_history_method_result[0]["ip"] . PHP_EOL . PHP_EOL .

                "\t\e[1;31;40m[+] Date : \e[1;36;40m" . $dns_history_method_result[0]["date"] . "\e[1;37;40m" . PHP_EOL . PHP_EOL;


            echo "    Do you Want to Continue Detection ? (y/n)" . PHP_EOL . PHP_EOL;

            echo "    → ";

            $handler = fopen("php://stdin", "r");
            $continue = fgets($handler);
            $continue = trim(strtolower($continue));

            if ($continue != "y") {
                $this->progressBar(100, 100);
                return false;
            } else {
                $this->results['method_1'] = $dns_history_method_result[0]["ip"];
            }
        } else {
            if ($dns_history_connection_status) {
                echo "\e[1;31;40m[*] Result : \e[1;36;40mPassed\e[1;37;40m" . PHP_EOL . "\e[1;37;40m    ";
            } else {
                $this->results['method_1'] = "Connection Error";
                echo "\e[1;31;40m[*] Result : \e[1;36;40mConnection Error\e[1;37;40m" . PHP_EOL . "\e[1;37;40m    ";
            }
        }

        return true;
    }

    public function method2($hostname)
    {
        echo PHP_EOL . "    \e[1;31;40m[*] Method 2 : \e[1;36;40mChecking Guardiran SSL Certificate Database (" . number_format($this->private_db_status['ssl_db']) . " Records) \e[1;37;40m" . PHP_EOL . "    ";

        sleep(1);

        $SSLCheckUp = new SSLCheckupController();
        $ssl_checkup_method_result = $SSLCheckUp->SSLCheckUp($hostname);
        $ssl_checkup_connection_status = $SSLCheckUp->getConnectionStatus();

        if ($ssl_checkup_method_result != NULL && $ssl_checkup_method_result != "false" && is_array($ssl_checkup_method_result)) {
            echo PHP_EOL . "\e[1;31;40m[*] Result :" . PHP_EOL . PHP_EOL .

                "\t\e[1;31;40mDirect IP Found !" . PHP_EOL . PHP_EOL .

                "\t\e[1;31;40m[+] Original IP : \e[1;36;40m" . $ssl_checkup_method_result[0]["ip"] . PHP_EOL . PHP_EOL;


            echo "    Do you Want to Continue Detection ? (y/n)" . PHP_EOL . PHP_EOL;
            echo "    → ";
            $handler = fopen("php://stdin", "r");
            $continue = fgets($handler);
            $continue = trim(strtolower($continue));

            if ($continue != "y") {
                $this->progressBar(100, 100);
                return false;
            } else {
                $this->results['method_2'] = $ssl_checkup_method_result[0]["ip"];
            }
        } else {
            if ($ssl_checkup_connection_status) {
                echo PHP_EOL . "    \e[1;31;40m[*] Result : \e[1;36;40mPassed\e[1;37;40m" . PHP_EOL . "\e[1;37;40m    ";
            } else {
                $this->results['method_2'] = "Connection Error";
                echo PHP_EOL . "    \e[1;31;40m[*] Result : \e[1;36;40mConnection Error\e[1;37;40m" . PHP_EOL . "\e[1;37;40m    ";
            }
        }

        return true;
    }

    public function method3($hostname)
    {

        echo PHP_EOL . "    \e[1;31;40m[*] Method 3 : \e[1;36;40mCross-Site Port Attack \e[1;37;40m" . PHP_EOL . "    ";

        sleep(1);

        $XSPA = new XSPAController();
        $xsp_attack_result = $XSPA->XSPA($hostname);
        $xsp_attack_connection_status = $XSPA->getConnectionStatus();

        if ($xsp_attack_result != NULL && $xsp_attack_result != "false" && is_array($xsp_attack_result)) {
            if (isset($xsp_attack_result['main'])) {
                echo PHP_EOL . "    \e[1;31;40m[*] Result :" . PHP_EOL . PHP_EOL .

                    "\t\e[1;31;40mDirect IP Found !" . PHP_EOL . PHP_EOL .

                    "\t\e[1;31;40m[+] Original IP : \e[1;36;40m" . $xsp_attack_result['main'] . "\e[1;37;40m" . PHP_EOL . PHP_EOL;


                echo "    Do you Want to Continue Detection ? (y/n)" . PHP_EOL . PHP_EOL;
                echo "    → ";
                $handler = fopen("php://stdin", "r");
                $continue = fgets($handler);
                $continue = trim(strtolower($continue));

                if ($continue != "y") {
                    $this->progressBar(100, 100);
                    return false;
                } else {
                    $this->results['method_3'] = $xsp_attack_result['main'];
                }
            } else {
                echo PHP_EOL . "    \e[1;31;40m[*] Result : \e[1;36;40mPassed\e[1;37;40m" . PHP_EOL . "\e[1;37;40m    ";
            }

        } else {
            if ($xsp_attack_connection_status) {
                echo PHP_EOL . "    \e[1;31;40m[*] Result : \e[1;36;40mPassed\e[1;37;40m" . PHP_EOL . "\e[1;37;40m    ";
            } else {
                $this->results['method_3'] = "Connection Error";
                echo PHP_EOL . "    \e[1;31;40m[*] Result : \e[1;36;40mConnection Error\e[1;37;40m" . PHP_EOL . "\e[1;37;40m    ";
            }
        }

        return true;
    }

    public function method4($hostname)
    {
        echo PHP_EOL . "    \e[1;31;40m[*] Method 4 : \e[1;36;40mCheck Sub domains" . PHP_EOL . "\e[1;37;40m    ";

        sleep(1);

        $sub_domain = new SubDomainController($hostname);

        $sub_domain_check_connection_status = $sub_domain->getConnectionStatus();

        echo PHP_EOL . "    \e[1;37;40mChoose Number to Start Subdomain Scan (1 - " . $sub_domain->getSubDomainCount() . ")" . PHP_EOL . PHP_EOL;
        echo "    → ";
        $handler = fopen("php://stdin", "r");
        $subdomains_number = fgets($handler);
        $subdomains_number = trim($subdomains_number);

        if (Validate::validate($subdomains_number, "required|method:digit|limit:1-20")) {

            $subdomains_number = (int) $subdomains_number;

            if ($subdomains_number > $sub_domain->getSubDomainCount()) {
                $subdomains_number = $sub_domain->getSubDomainCount();
            } else if ($subdomains_number < 1) {
                $subdomains_number = 1;
            }

            $sub_domain->sliceSubdomains($subdomains_number);
            $sub_domain_check_result = $sub_domain->subDomainCheckUp($hostname);
        } else {
            $this->results['method_4'] = "UnChecked";
            echo PHP_EOL . "    \e[1;31;40m[-] Wrong Input " . PHP_EOL . PHP_EOL;
            echo "    \e[1;31;40m[*] Result : \e[1;36;40mUnChecked\e[1;37;40m" . PHP_EOL;
            return false;
        }


        if ($sub_domain_check_result != NULL && $sub_domain_check_result != "false") {

            $this->results['method_4'] = $sub_domain_check_result['server_ip'];
            $this->database_controller->infiniteDatabase($hostname, $sub_domain_check_result['server_ip'], $sub_domain_check_result['subdomain']);

            echo PHP_EOL . "    \e[1;31;40m[*] Result :" . PHP_EOL . PHP_EOL .

                "\t\e[1;31;40mDirect IP Found !" . PHP_EOL . PHP_EOL .

                "\t\e[1;31;40m[+] Original IP : \e[1;36;40m" . $sub_domain_check_result['server_ip'] . PHP_EOL . PHP_EOL .

                "\t\e[1;31;40m[+] Sub domain : \e[1;36;40m" . $sub_domain_check_result['subdomain'] . "\e[1;37;40m" . PHP_EOL;
        } else {
            if ($sub_domain_check_connection_status) {
                echo PHP_EOL . "    \e[1;31;40m[*] Result : \e[1;36;40mPassed\e[1;37;40m" . PHP_EOL . "\e[1;37;40m    ";
            } else {
                $this->results['method_4'] = "Connection Error";
                echo PHP_EOL . "    \e[1;31;40m[*] Result : \e[1;36;40mConnection Error\e[1;37;40m" . PHP_EOL . "\e[1;37;40m    ";
            }
        }

        return true;
    }


    public function progressBar($done, $total, $info = "", $width = 50)
    {
        $perc = round(($done * 100) / $total);
        $bar = round(($width * $perc) / 100);
        echo PHP_EOL . "    " . sprintf("%s%% [%s>%s]%s\r", $perc, str_repeat("=", $bar), str_repeat(" ", $width - $bar), $info) . PHP_EOL . "    ";
    }

    public function isConnected()
    {
        $connected = @fsockopen("www.google.com", 80);
        if ($connected) {
            fclose($connected);
            return true;
        }
        return false;
    }


    public function serverUp()
    {
        $connected = @fsockopen("www.guardiran.org", 80);
        if ($connected) {
            fclose($connected);
            return true;
        }
        return false;
    }

}
