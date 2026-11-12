
#include <stdio.h>
#include <string.h>

int main(int argc, char *argv[])
{
    unsigned char buffer[16];
    FILE *file_pointer;
    size_t bytesRead;
    size_t i;
    unsigned long offset = 0;

    if (argc < 2) {
        printf("Please enter a file name.\n");
        return 1;
    }

    if (strcmp(argv[1], "-h") == 0 || strcmp(argv[1], "--help") == 0) {
        printf("Hexadecimal File Viewer\n");
        printf("Usage: %s <file>\n", argv[0]);
        printf("Displays file contents in Hex and ASCII.\n");
        return 0;
    }

    file_pointer = fopen(argv[1], "rb");

    if (file_pointer == NULL) {
        printf("Error: Cannot open file.\n");
        return 1;
    }

    printf("Offset   Hexadecimal Values                              ASCII\n");
    printf("--------------------------------------------------------------------------\n");

    while ((bytesRead = fread(buffer, 1, 16, file_pointer)) > 0) {

        printf("%08lX   ", offset);

        for (i = 0; i < 16; i++) {
            if (i < bytesRead) {
                printf("%02X ", buffer[i]);
            }
            else {
                printf("   ");
            }

            if (i == 7) {
                printf(" ");
            }
        }

        printf(" |");

        for (i = 0; i < bytesRead; i++) {
            if (buffer[i] >= 32 && buffer[i] <= 126) {
                printf("%c", buffer[i]);
            }
            else {
                printf(".");
            }
        }

        printf("|\n");

        offset += bytesRead;
    }

    if (ferror(file_pointer)) {
        printf("Error: Failed to read file.\n");
    }

    fclose(file_pointer);

    return 0;
}
