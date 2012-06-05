#ifndef ALFRED_JSON
#define ALFRED_JSON

#include <json.h>

char *alfred_json_get_string(json_object *obj, const char *id);
json_object *alfred_json_create(int code, const char *message, json_object *data);
void alfred_json_free(json_object *data);
void alfred_json_display(json_object *data);
void alfred_json_create_display(int code, const char *message, json_object *data);

#endif
