
#include <stdio.h>
#include <string.h>
#include <ctype.h>

void checkPassword(char password[]) {

    int upper = 0;
    int lower = 0;
    int digit = 0;
    int symbol = 0;
    int points = 0;

    int length = strlen(password);

    printf("\nPassword Length: %d\n", length);

    if (length >= 8) {
        points++;
        printf("Length: Passed\n");
    } else {
        printf("Length: Failed\n");
    }

    for (int i = 0; i < length; i++) {

        if (isupper(password[i])) {
            upper = 1;
        }
        else if (islower(password[i])) {
            lower = 1;
        }
        else if (isdigit(password[i])) {
            digit = 1;
        }
        else {
            symbol = 1;
        }
    }

    if (upper) {
        points++;
        printf("Uppercase: Passed\n");
    } else {
        printf("Uppercase: Failed\n");
    }

    if (lower) {
        points++;
        printf("Lowercase: Passed\n");
    } else {
        printf("Lowercase: Failed\n");
    }

    if (digit) {
        points++;
        printf("Number: Passed\n");
    } else {
        printf("Number: Failed\n");
    }

    if (symbol) {
        points++;
        printf("Symbol: Passed\n");
    } else {
        printf("Symbol: Failed\n");
    }

    printf("\nTotal Points: %d/5\n", points);

    if (points <= 2) {
        printf("Password Strength: Weak\n");
    }
    else if (points <= 4) {
        printf("Password Strength: Acceptable\n");
    }
    else {
        printf("Password Strength: Strong\n");
    }
}

int main() {

    char password[100];

    printf("Enter your password: ");
    scanf("%99s", password);

    checkPassword(password);

    return 0;
}
