# Dataset attribution

Dataset: `HttpParamsDataset` by Morzeux, retrieved from the upstream repository.

- Repository: https://github.com/Morzeux/HttpParamsDataset
- Files used: `payload_train.csv` and `payload_test.csv`
- License: see `HTTPPARAMS_LICENSE.txt` in this directory.
- Record fields: HTTP parameter value (`payload`), string length, attack subtype, and normal/anomalous label.
- Scope note: this is a parameter-value corpus. It does not contain full request/response behavior or labels for IDOR and CSRF.

The test split is kept separate from pair training. Do not move test examples into the training file when reporting metrics.
