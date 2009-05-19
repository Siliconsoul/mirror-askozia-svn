/*
 * Copyright (C) 1991-2, RSA Data Security, Inc. Created 1991. All
 * rights reserved.
 * 
 * License to copy and use this software is granted provided that it
 * is identified as the "RSA Data Security, Inc. MD5 Message-Digest
 * Algorithm" in all material mentioning or referencing this software
 * or this function.
 * 
 * License is also granted to make and use derivative works provided
 * that such works are identified as "derived from the RSA Data
 * Security, Inc. MD5 Message-Digest Algorithm" in all material
 * mentioning or referencing the derived work.
 * 
 * RSA Data Security, Inc. makes no representations concerning either
 * the merchantability of this software or the suitability of this
 * software for any particular purpose. It is provided "as is"
 * without express or implied warranty of any kind.
 * These notices must be retained in any copies of any part of this
 * documentation and/or software.
 *
 * --- T2-COPYRIGHT-NOTE-BEGIN ---
 * This copyright note is auto-generated by ./scripts/Create-CopyPatch.
 *
 * T2 SDE: misc/luabash/md5/md5.c
 * Copyright (C) 2006 The T2 SDE Project
 * Copyright (C) 1991-2, RSA Data Security, Inc. Created 1991.
 * Copyright (C) 2006 Felix von Leitner
 *
 * More information can be found in the files COPYING and README.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; version 2 of the License. A copy of the
 * GNU General Public License can be found in the file COPYING.
 * --- T2-COPYRIGHT-NOTE-END ---
 */


#ifndef _MD5_H
#define _MD5_H

#include <sys/types.h>
#include <inttypes.h>

__BEGIN_DECLS

/*
   Define the MD5 context structure.
   Please DO NOT change the order or contents of the structure as
   various assembler files depend on it !!
*/
typedef struct {
  uint32_t state[4];       /* state (ABCD) */
  uint32_t count[2];       /* number of bits, modulo 2^64 (least sig word first) */
  uint8_t  buffer[64];     /* input buffer for incomplete buffer data */
} MD5_CTX;

void MD5Init(MD5_CTX* ctx);
void MD5Update(MD5_CTX* ctx, const uint8_t* buf, size_t len);
void MD5Final(uint8_t digest[16], MD5_CTX* ctx);

char* md5crypt(const char* pw, const char* salt);

__END_DECLS

#endif

