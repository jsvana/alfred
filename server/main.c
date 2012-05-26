#include <stdlib.h>
#include <stdio.h>
#include <string.h>

#include <json.h>
#include <glib.h>

// Lets me check the _ref_count
#include <json_object_private.h>

#include "json.h"
#include "error.h"

// Include modules
#include "alfred.h"
#include "location.h"
#include "network.h"
#include "password.h"
#include "xbmc.h"

#define CHUNK_SIZE 1024

// TODO: Actually pass the version of the protocol somewhere

char *alfred_read_file(FILE *file) {
	char *buf = g_malloc(sizeof(char) * (CHUNK_SIZE + 1));
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
	printf("Content-type: text/plain\n\n");
	char *data = alfred_read_file(stdin);
	enum json_tokener_error err;

	json_object *json = json_tokener_parse_verbose(data, &err);
	
	// Shouldn't need this var any more
	g_free(data);

	// Parse error
	if (err != json_tokener_success) {
		alfred_error_static(ALFRED_ERROR_MALFORMED_COMMAND);
		return 0;
	}

	char *method = alfred_json_get_string(json, "method");
	char *alfred = alfred_json_get_string(json, "alfred");
	json_object *params = json_object_object_get(json, "params");

	if (!method || !alfred || !params) {
		alfred_error_static(ALFRED_ERROR_MALFORMED_COMMAND);
		g_free(method);
		g_free(alfred);
		if (params) {
			json_object_put(params);
		}

		return 0;
	}

	gchar **info = g_strsplit(method, ".", 2);

	if (info && info[0] && info[1]) {
		if (strcmp(info[0], "Alfred") == 0) {
			alfred_module_alfred(info[1], params);
		} else if (strcmp(info[0], "Location") == 0) {
			alfred_module_location(info[1], params);
		} else if (strcmp(info[0], "Network") == 0) {
			alfred_module_network(info[1], params);
		} else if (strcmp(info[0], "Password") == 0) {
			alfred_module_password(info[0], params);
		} else if (strcmp(info[0], "XBMC") == 0) {
			alfred_module_xbmc(info[0], params);
		} else {
			alfred_error_static(ALFRED_ERROR_UNKNOWN_COMMAND);
		}
	} else {
		alfred_error_static(ALFRED_ERROR_UNKNOWN_COMMAND);
	}
	
	g_strfreev(info);
	g_free(method);
	g_free(alfred);

	json_object_put(json);

	return 0;
}
