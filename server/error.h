#ifndef ALFRED_ERROR
#define ALFRED_ERROR

typedef enum alfred_error alfred_error;
enum alfred_error {
	ALFRED_ERROR_OK = 0,
	ALFRED_ERROR_MALFORMED_COMMAND = 1,
	ALFRED_ERROR_UNKNOWN_COMMAND = 2,
	ALFRED_ERROR_NOT_AUTHENTICATED = 3,
	ALFRED_ERROR_INCORRECT_PARAMS = 4,
	ALFRED_ERROR_METHOD_FAILED = 5,
	ALFRED_ERROR_INTERNAL_SERVER = 6,

	// This is for internal use
	ALFRED_ERROR_COUNT = 7,
};

void alfred_error_static(alfred_error err);

#endif
