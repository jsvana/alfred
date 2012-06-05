#include <stdio.h>
#include <json.h>
#include <glib.h>

#include "json.h"

char *alfred_json_get_string(json_object *obj, const char *id) {
	json_object *str_obj = json_object_object_get(obj, id);
	if (!str_obj) {
		return NULL;
	}

	char *str = g_strdup(json_object_get_string(str_obj));

	return str;
}

json_object *alfred_json_create(int code, const char *message, json_object *data) {
	json_object *obj = json_object_new_object();
	json_object *code_obj = json_object_new_int(code);
	json_object *msg = json_object_new_string(message);
	if (!data) {
		data = json_object_new_object();
	}

	json_object_object_add(obj, "code", code_obj);
	json_object_object_add(obj, "message", msg);
	json_object_object_add(obj, "data", data);

	return obj;
}

void alfred_json_free(json_object *data) {
	json_object_put(data);
}

void alfred_json_display(json_object *data) {
	printf("%s", json_object_to_json_string(data));
}

void alfred_json_create_display(int code, const char *message, json_object *data) {
	json_object *obj = alfred_json_create(code, message, data);
	alfred_json_display(obj);
	alfred_json_free(data);
}
