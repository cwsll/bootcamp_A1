
import socket
import ipaddress


# Scanner Settings
TIMEOUT = 3
BUFFER_SIZE = 1024

DEFAULT_PORTS = [21, 22, 25, 80, 110, 143, 8080, 8888]
HTTP_PORTS = [80, 8000, 8080, 8888]


def grab_banner(target_ip, port):

    try:
        with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as sock:

            sock.settimeout(TIMEOUT)
            sock.connect((target_ip, port))

            # Send HTTP request for web services
            if port in HTTP_PORTS:

                request = (
                    f"HEAD / HTTP/1.1\r\n"
                    f"Host: {target_ip}\r\n"
                    f"Connection: close\r\n\r\n"
                )

                sock.sendall(request.encode())

            # Receive service banner
            banner = sock.recv(BUFFER_SIZE)

            if banner:
                banner_text = banner.decode(
                    "utf-8", errors="ignore"
                ).strip()

                lines = banner_text.splitlines()

                if lines:
                    return lines[0]

            return None

    except socket.timeout:
        return "Timeout: No response"

    except ConnectionRefusedError:
        return "Connection refused"

    except OSError:
        return "Connection failed"


def scan_services(target_ip, ports):

    print("\n" + "=" * 50)
    print("             BANNER GRABBING TOOL")
    print("=" * 50)
    print(f"Target IP: {target_ip}")
    print(f"Ports    : {len(ports)}")
    print("-" * 50)

    for port in ports:

        print(f"\n[PORT {port}] ", end="")

        banner = grab_banner(target_ip, port)

        if banner is None:
            print("No banner received")

        elif banner.startswith("Timeout"):
            print(banner)

        elif banner.startswith("Connection"):
            print(banner)

        else:
            print(banner)

    print("\n" + "=" * 50)
    print("             SCAN COMPLETED")
    print("=" * 50)


def main():

    print("=" * 50)
    print("             BANNER GRABBING TOOL")
    print("=" * 50)

    target_ip = input("Enter Target IP: ").strip()

    try:
        ipaddress.IPv4Address(target_ip)

    except ipaddress.AddressValueError:
        print("Error: Invalid IPv4 address.")
        return

    ports_input = input(
        "Enter ports (comma separated) or press Enter for defaults: "
    ).strip()

    if ports_input:

        try:
            ports = sorted(set(
                int(port.strip())
                for port in ports_input.split(",")
            ))

            if not ports or any(port < 1 or port > 65535 for port in ports):
                print("Error: Ports must be between 1 and 65535.")
                return

        except ValueError:
            print("Error: Please enter valid port numbers.")
            return

    else:
        ports = DEFAULT_PORTS

    scan_services(target_ip, ports)


if __name__ == "__main__":
    main()
