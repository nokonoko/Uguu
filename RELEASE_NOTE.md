## Uguu 1.8.6

### Whats new

* Includes INDEX creation in the dbSchemas files, this greatly improves performance when performing filename generation, antidupe, blacklist or rate-limit checks against the database,
  especially on big databases. It's recommended you follow the instructions below on how to add INDEX.
* time() is called once in connector to get a timestamp instead of multiple times.
* The function `diverseArray` is now called `transposeArray`, the variables within the function are also renamed to make it easier to understand.
* The function `uploadFile` performs a check if `BENCHMARK_MODE` is set in the configuration, if it is the file will not be uploaded.
* Benchmarking capbility added.
* Docs updated with how to use [Benchmarking](https://github.com/nokonoko/Uguu/wiki/Benchmarking) and also a [Optimization Guide](https://github.com/nokonoko/Uguu/wiki/Optimization).

### Breaking changes

* config.json must include the `"BENCHMARK_MODE"` value, should be set to `false` when not benchmarking, otherwise file(s) will not be uploaded.

### Add INDEX to an existing Uguu installation

#### SQLite

```
CREATE INDEX files_hash_idx ON files (hash);
CREATE INDEX files_name_idx ON files (filename);
CREATE INDEX ratelimit_iphash_idx ON ratelimit (iphash);
CREATE INDEX blacklist_hash_idx ON blacklist (hash);
```

#### PostgreSQL

```
CREATE INDEX files_hash_idx ON files (hash);
CREATE INDEX files_name_idx ON files (filename);
CREATE INDEX ratelimit_iphash_idx ON ratelimit (iphash);
CREATE INDEX blacklist_hash_idx ON blacklist (hash);
```

#### MySQL

```
CREATE INDEX files_hash_idx ON files (hash);
CREATE INDEX files_name_idx ON files (filename);
CREATE INDEX ratelimit_iphash_idx ON ratelimit (iphash);
CREATE INDEX blacklist_hash_idx ON blacklist (hash);
```