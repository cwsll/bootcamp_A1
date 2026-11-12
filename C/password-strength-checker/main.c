#include <stdio.h>
#include <string.h>
#include <ctype.h>

int main() {
    char password[100];
    int hasUpper = 0, hasLower = 0, hasNumber = 0, hasSymbol = 0;

    printf("Enter password: ");
    fgets(password, sizeof(password), stdin);

    password[strcspn(password, "\n")] = '\0';

    int length = strlen(password);

    for (int i = 0; i < length; i++) {
        if (isupper((unsigned char)password[i]))
            hasUpper = 1;
        else if (islower((unsigned char)password[i]))
            hasLower = 1;
        else if (isdigit((unsigned char)password[i]))
            hasNumber = 1;
        else
            hasSymbol = 1;
    }

    if (length > 8 && hasUpper && hasLower && hasNumber && hasSymbol)
        printf("Password is Strong\n");
    else
        printf("Password is Weak\n");

    return 0;
}