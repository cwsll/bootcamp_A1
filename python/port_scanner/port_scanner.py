
import socket
import time
import ipaddress
from concurrent.futures import ThreadPoolExecutor, as_completed


# Scanner Settings
MAX_WORKERS = 50
TIMEOUT = 0.5
START_PORT = 1
END_PORT = 1024


def scan_port(target_ip, port):
    try:
        with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as sock:
            sock.settimeout(TIMEOUT)

            result = sock.connect_ex((target_ip, port))

            if result == 0:
                return port

    except OSError:
        pass

    return None


def scan_ports(target_ip):

    open_ports = []
    total_ports = END_PORT - START_PORT + 1

    print("\n" + "=" * 45)
    print("          MULTITHREADED PORT SCANNER")
    print("=" * 45)
    print(f"Target IP : {target_ip}")
    print(f"Port Range: {START_PORT}-{END_PORT}")
    print(f"Threads   : {MAX_WORKERS}")
    print("-" * 45)

    start_time = time.perf_counter()

    with ThreadPoolExecutor(max_workers=MAX_WORKERS) as executor:

        tasks = {
            executor.submit(scan_port, target_ip, port): port
            for port in range(START_PORT, END_PORT + 1)
        }

        for task in as_completed(tasks):

            port = task.result()

            if port is not None:
                open_ports.append(port)
                print(f"[OPEN] Port {port}")

    elapsed_time = time.perf_counter() - start_time

    open_ports.sort()

    closed_ports = total_ports - len(open_ports)

    print("\n" + "=" * 45)
    print("              SCAN REPORT")
    print("=" * 45)
    print(f"Target IP    : {target_ip}")
    print(f"Scanned Ports: {total_ports}")
    print(f"Open Ports   : {len(open_ports)}")
    print(f"Other Results: {closed_ports}")
    print(f"Time Taken   : {elapsed_time:.2f} seconds")

    if open_ports:
        print("\nOpen Ports List:")
        for port in open_ports:
            print(f"  - {port}")
    else:
        print("\nNo open ports found.")

    print("=" * 45)
    print("            SCAN COMPLETED")
    print("=" * 45)


def main():

    print("=" * 45)
    print("          MULTITHREADED PORT SCANNER")
    print("=" * 45)

    target_ip = input("Enter Target IP: ").strip()

    try:
        ipaddress.IPv4Address(target_ip)

    except ipaddress.AddressValueError:
        print("\nError: Please enter a valid IPv4 address.")
        return

    try:
        scan_ports(target_ip)

    except KeyboardInterrupt:
        print("\nScan interrupted by user.")


if __name__ == "__main__":
    main()
