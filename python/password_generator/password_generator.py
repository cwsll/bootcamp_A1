
import secrets
import string
import hashlib




def show_header():

    print("\n" + "=" * 55)
    print("              CYBER SECURITY TOOLKIT")
    print("          PASSWORD GENERATOR & SHA-256")
    print("=" * 55)


def generate_password(length):

    characters = (
        string.ascii_letters
        + string.digits
        + string.punctuation
    )

    password = [
        secrets.choice(string.ascii_lowercase),
        secrets.choice(string.ascii_uppercase),
        secrets.choice(string.digits),
        secrets.choice(string.punctuation)
    ]

    password += [
        secrets.choice(characters)
        for _ in range(length - 4)
    ]

    secrets.SystemRandom().shuffle(password)

    return "".join(password)


def calculate_hash(text):

    text_bytes = text.encode("utf-8")

    hash_value = hashlib.sha256(text_bytes).hexdigest()

    return hash_value


def password_generator():

    print("\n" + "-" * 55)
    print("              PASSWORD GENERATOR")
    print("-" * 55)

    try:
        length = int(input("Enter password length: "))

        if length < 4 or length > 128:
            print("\n[ERROR] Length must be between 4 and 128.")
            return

        password = generate_password(length)

        print("\n" + "=" * 55)
        print("             GENERATED PASSWORD")
        print("=" * 55)
        print(f"\n  {password}")
        print(f"\n  Password Length: {len(password)}")
        print("=" * 55)

    except ValueError:
        print("\n[ERROR] Please enter a valid number.")


def hash_calculator():

    print("\n" + "-" * 55)
    print("               SHA-256 HASHER")
    print("-" * 55)

    text = input("Enter text to hash: ")

    if not text:
        print("\n[ERROR] Text cannot be empty.")
        return

    hash_value = calculate_hash(text)

    print("\n" + "=" * 55)
    print("               SHA-256 RESULT")
    print("=" * 55)
    print(f"\nOriginal Text: {text}")
    print(f"\nSHA-256 Hash:\n{hash_value}")
    print("\nHash Length: 64 hexadecimal characters")
    print("=" * 55)


def main():

    while True:

        show_header()

        print("\n[1] Generate Secure Password")
        print("[2] Calculate SHA-256 Hash")
        print("[3] Exit")

        print("\n" + "-" * 55)

        choice = input("Select an option: ").strip()

        if choice == "1":
            password_generator()

        elif choice == "2":
            hash_calculator()

        elif choice == "3":
            print("\nThank you for using Cyber Security Toolkit!")
            print("Exiting program...")
            break

        else:
            print("\n[ERROR] Invalid choice. Please select 1, 2, or 3.")

        input("\nPress Enter to return to the main menu...")


if __name__ == "__main__":
    main()
