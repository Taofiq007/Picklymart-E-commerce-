# Sample data

These rows are fictional and for smoke testing only; do not treat them as real Picklymart data.

- `categories.csv`: `source_id,name,slug,parent_source_id,description,status`
- `brands.csv`: `source_id,name,slug,status`
- `products.csv`: `source_id,sku,slug,name,description,category_source_id,brand_source_id,price,sale_price,currency,status`
- `media_assets.csv`: `source_id,filename,original_name,disk,path,mime_type,size_bytes,checksum,status`
- `product_media.csv`: `product_source_id,media_source_id,role,position`
- `category_media.csv`: `category_source_id,media_source_id,role,position`

Before using real data, map the source export headers to these fields and inspect the output. Every imported product/category/brand should have a stable `source_id`.

To smoke-test with the sample CSVs, copy `sample-data/media/` contents into `storage/app/public/media/` before importing the CSVs. The included PNG is a fictional placeholder, not a real Picklymart asset.
