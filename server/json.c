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
