# Symfony Azure Blob Storage Client (Azurite)

Minimal web client for Azure Blob Storage built with Symfony.

Features:
- Upload file
- List blobs (with optional prefix filtering)
- Download blob
- Delete blob

### Azure vs Azurite

This project uses **Azurite** for local development in order to avoid Azure subscription requirements.

Azurite is the official Azure Storage emulator and implements the same Blob Storage API surface used by the Azure SDK.

Switching to a real Azure account only requires updating the environment variables:

- `AZURE_STORAGE_ACCOUNT_NAME`
- `AZURE_STORAGE_ACCOUNT_KEY`
- `AZURE_STORAGE_BLOB_ENDPOINT`

## Architecture
- `src/Domain` - storage contract and domain exceptions
- `src/Application` - use cases/actions (`UploadBlob`, `ListBlobs`, `DownloadBlob`, `DeleteBlob`)
- `src/Infrastructure` - Azure Blob SDK adapter
- `src/Presentation` - HTTP controllers + Twig templates

## Local setup (Docker + Azurite)

### 1) Configure environment
Get Azure storage account key [from here](https://learn.microsoft.com/en-us/azure/storage/common/storage-connect-azurite?tabs=blob-storage) (starting  `Eby8vdM02x...`)

Open `.env`, and set it in the `AZURE_STORAGE_ACCOUNT_KEY` variable

### 2) Run containers
```bash
docker compose up -d --build
```
### 3) Install dependencies
```bash
docker compose exec php bash
```
Then, inside the container:
```bash
composer install
```

### 4) Create container (once)
```bash
bin/console azure:blob:init
```

### 5) Access the app
Open `http://localhost:8000` in your browser. You can upload files, list and delete blobs

### Notes

Blobs can be grouped using a prefix (simulated folder).

To upload a file into a specific prefix:

1. Enter the desired prefix in the “Prefix” field.
2. Click **Filter**.
3. Upload the file.

The upload form preserves the current prefix and stores the blob under:

`<prefix>/<generated_id>_<filename>`

If no prefix is provided, the file is uploaded at the root level.
