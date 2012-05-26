#include <stdlib.h>
#include <stdio.h>

#include <json.h>
#include <glib.h>

// Lets me check the _ref_count
#include <json_object_private.h>

#define CHUNK_SIZE 1024

char *alfred_read_file(FILE *file) {
	char *buf = malloc(sizeof(char) * (CHUNK_SIZE + 1));
	int count = 1, location = 0;
	size_t nbytes;

	while ((nbytes = fread(&buf[location], sizeof(char), CHUNK_SIZE, file)) == CHUNK_SIZE) {
		location += nbytes;
		buf = realloc(buf, sizeof(char) * ((++count * CHUNK_SIZE) + 1));
	}

	// We want the actual size
	location += nbytes;

	// Just in case...
	buf[location] = '\0';

	return buf;
}

int main(int argc, char *argv[]) {
	char *data = alfred_read_file(stdin);
	char *t;
	json_object *json = json_tokener_parse_verbose(data, &t);

	printf("%s\n", t);

	if (!json) {
		printf(":(\n");
		return 1;
	}
	json_object *method = json_object_object_get(json, "method");

	gchar **info = g_strsplit(json_object_get_string(method), ".", 2);

	if (info) {
		g_strfreev(info);
	} else {
		// TODO: Error
		// -1: Malformed command
	}

	json_object_put(method);
	json_object_put(json);
	
	free(data);

	return 0;
}
