
import re
from collections import Counter


# Analyzer Settings
LOG_FILE = "access.log"

PATTERNS = {
    "SQL Injection": re.compile(
        r"UNION\s+SELECT|OR\s+['\"]?\d+['\"]?\s*=\s*['\"]?\d+",
        re.IGNORECASE
    ),

    "Path Traversal": re.compile(
        r"\.\./|%2e%2e%2f|/etc/passwd",
        re.IGNORECASE
    )
}


def analyze_log(log_file):

    alerts = []
    attack_counts = Counter()

    print("\n" + "=" * 55)
    print("              AUTOMATED LOG ANALYZER")
    print("=" * 55)
    print(f"Log File: {log_file}")
    print("-" * 55)

    try:
        with open(log_file, "r", encoding="utf-8", errors="ignore") as file:

            for line_number, line in enumerate(file, start=1):

                ip_match = re.search(
                    r"^\s*(\d{1,3}(?:\.\d{1,3}){3})",
                    line
                )

                if not ip_match:
                    continue

                attacker_ip = ip_match.group(1)

                for attack_name, pattern in PATTERNS.items():

                    if re.search(pattern, line):

                        alert = {
                            "type": attack_name,
                            "line": line_number,
                            "ip": attacker_ip
                        }

                        alerts.append(alert)
                        attack_counts[attack_name] += 1

                        print("\n[!] SECURITY ALERT")
                        print(f"Attack Type : {attack_name}")
                        print(f"Source IP   : {attacker_ip}")
                        print(f"Line Number : {line_number}")
                        print("-" * 55)

    except FileNotFoundError:
        print(f"\nError: File '{log_file}' not found.")
        return

    except OSError as error:
        print(f"\nError reading file: {error}")
        return

    # Final Report
    print("\n" + "=" * 55)
    print("                  SCAN REPORT")
    print("=" * 55)

    print(f"Total Attacks Detected: {len(alerts)}")

    if attack_counts:

        print("\nAttack Statistics:")

        for attack_name, count in attack_counts.items():
            print(f"{attack_name:<25}: {count}")

    else:
        print("\nNo suspicious activity detected.")

    print("=" * 55)
    print("                ANALYSIS COMPLETED")
    print("=" * 55)


def main():

    print("=" * 55)
    print("              AUTOMATED LOG ANALYZER")
    print("=" * 55)

    analyze_log(LOG_FILE)


if __name__ == "__main__":
    main()
