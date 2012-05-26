PROG = alfred.rpc
OBJS = main.o json.o error.o
MODULES = alfred.o location.o network.o password.o xbmc.o
LIBS = json glib-2.0

CC = gcc
CFLAGS = -ggdb -Wall -ggdb $(shell pkg-config --cflags ${LIBS})
LDFLAGS = $(shell pkg-config --libs ${LIBS}) -Wl,-rpath -Wl,/usr/local/lib

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
