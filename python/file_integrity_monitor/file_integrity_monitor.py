
import hashlib
import time
import os


# ==========================================
#          FILE INTEGRITY MONITOR
# ==========================================

CHUNK_SIZE = 4096


def calculate_hash(file_path):

    sha256 = hashlib.sha256()

    with open(file_path, "rb") as file:

        while True:
            chunk = file.read(CHUNK_SIZE)

            if not chunk:
                break

            sha256.update(chunk)

    return sha256.hexdigest()


def monitor_file():

    print("\n" + "=" * 60)
    print("             FILE INTEGRITY MONITOR")
    print("              SECURITY TOOL")
    print("=" * 60)

    file_path = input("\nEnter file name or full path: ").strip().strip('"')

    if not os.path.isfile(file_path):
        print("\n[ERROR] File does not exist.")
        return

    try:
        interval = float(input("Enter monitoring duration in seconds: "))

        if interval <= 0:
            print("\n[ERROR] Time must be greater than zero.")
            return

    except ValueError:
        print("\n[ERROR] Please enter a valid number.")
        return

    print("\n" + "-" * 60)
    print(f"Target File       : {file_path}")
    print(f"Monitoring Time   : {interval} seconds")
    print("-" * 60)

    try:
        baseline_hash = calculate_hash(file_path)

    except OSError as error:
        print(f"\n[ERROR] Cannot read file: {error}")
        return

    print("\n[+] Baseline Hash Created Successfully.")
    print(f"[+] SHA-256: {baseline_hash}")

    print("\n" + "=" * 60)
    print("             MONITORING STARTED")
    print("=" * 60)

    start_time = time.time()

    try:

        while time.time() - start_time < interval:

            time.sleep(min(3, interval - (time.time() - start_time)))

            try:
                current_hash = calculate_hash(file_path)

                if current_hash != baseline_hash:

                    print("\n" + "!" * 60)
                    print("[CRITICAL ALERT] FILE MODIFICATION DETECTED!")
                    print("!" * 60)
                    print(f"File: {file_path}")
                    print("[!] Original Hash:")
                    print(baseline_hash)
                    print("\n[!] Current Hash:")
                    print(current_hash)
                    print("!" * 60)

                else:
                    print("[OK] No changes detected. File is safe.")

            except FileNotFoundError:
                print("\n[CRITICAL ALERT] FILE HAS BEEN DELETED!")

            except OSError as error:
                print(f"\n[ERROR] Cannot access file: {error}")

    except KeyboardInterrupt:
        print("\n[!] Monitoring interrupted by user.")

    print("\n" + "=" * 60)
    print("             MONITORING COMPLETED")
    print("=" * 60)
    print("[+] Monitoring time has ended.")
    print("[+] Program exited successfully.")


def main():

    monitor_file()


if __name__ == "__main__":
    main()
