import os
import re

directory = "d:/www/coursegrid/database/migrations"

for filename in os.listdir(directory):
    if filename.startswith("2026_09_02_0648") and filename.endswith(".php"):
        filepath = os.path.join(directory, filename)
        with open(filepath, "r") as f:
            content = f.read()
            
        if "public function down()" in content:
            continue
            
        match = re.search(r"Schema::create\('([^']+)'", content)
        if match:
            table_name = match.group(1)
            down_method = f"""
    public function down() {{
        Schema::dropIfExists('{table_name}');
    }}
"""
            # Inject down method before the closing brace of the class
            content = re.sub(r'(\s*)\}\s*;\s*$', down_method + r'\1};\n', content)
            
            with open(filepath, "w") as f:
                f.write(content)
            print(f"Added down() to {filename}")

print("Done.")
