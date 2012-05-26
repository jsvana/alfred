#include <stdio.h>
#include <json.h>

#include "error.h"

static char *alfred_error_text[ALFRED_ERROR_COUNT] = {
	"Ok",
	"Malformed command",
	"Unknown command",
};

void alfred_error_static(alfred_error err) {
	json_object *obj = json_object_new_object();
	json_object *code = json_object_new_int(-err);
	json_object *message = json_object_new_string(alfred_error_text[err]);
	json_object *data = json_object_new_object();

	json_object_object_add(obj, "code", code);
	json_object_object_add(obj, "message", message);
	json_object_object_add(obj, "data", data);

	printf("%s\n", json_object_to_json_string(obj));

	//printd("%d\n", obj

	//json_object_put(code);
	//json_object_put(message);
	//json_object_put(data);
	json_object_put(obj);
}
