PROG = alfred.rpc
OBJS = main.o json.o error.o sql.o utils.o
MODULES = alfred.o location.o network.o password.o xbmc.o
LIBS = json glib-2.0

CC = gcc
CFLAGS = -std=gnu99 -ggdb -Wall -ggdb $(shell pkg-config --cflags ${LIBS}) $(shell mysql_config --cflags) $(shell curl-config --cflags)
LDFLAGS = $(shell pkg-config --libs ${LIBS}) $(shell mysql_config --libs) $(shell curl-config --libs) -Wl,-rpath -Wl,/usr/local/lib

all: ${PROG}

${PROG}: ${OBJS} ${MODULES}
	@echo ${CC} -o ${PROG}
	@${CC} ${CFLAGS} -o $@ $+ ${LDFLAGS}

%.o: %.c
	@echo ${CC} -c $<
	@${CC} ${CFLAGS} -c $< -o $@

run: all
	./${PROG}

clean:
	rm -f ${PROG} ${OBJS} ${MODULES}

.PHONY: all clean install
