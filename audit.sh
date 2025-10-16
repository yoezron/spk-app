#!/bin/bash
echo "======================================"
echo "   SPK APP REPOSITORY AUDIT"
echo "======================================"
echo ""
pwd
echo ""

echo "CONTROLLERS:"
echo "--------------------------------------"
find app/Controllers -name "*.php" 2>/dev/null | sort

echo ""
echo "MODELS:"
echo "--------------------------------------"
find app/Models -name "*.php" 2>/dev/null | sort

echo ""
echo "VIEWS - LAYOUTS:"
echo "--------------------------------------"
find app/Views/layouts -name "*.php" 2>/dev/null | sort

echo ""
echo "VIEWS - COMPONENTS:"
echo "--------------------------------------"
find app/Views/components -name "*.php" 2>/dev/null | sort

echo ""
echo "VIEWS - ADMIN:"
echo "--------------------------------------"
find app/Views/admin -name "*.php" 2>/dev/null | sort

echo ""
echo "VIEWS - MEMBER:"
echo "--------------------------------------"
find app/Views/member -name "*.php" 2>/dev/null | sort

echo ""
echo "VIEWS - SUPER:"
echo "--------------------------------------"
find app/Views/super -name "*.php" 2>/dev/null | sort

echo ""
echo "VIEWS - AUTH:"
echo "--------------------------------------"
find app/Views/auth -name "*.php" 2>/dev/null | sort

echo ""
echo "VIEWS - PUBLIC:"
echo "--------------------------------------"
find app/Views/public -name "*.php" 2>/dev/null | sort

echo ""
echo "SERVICES:"
echo "--------------------------------------"
find app/Services -name "*.php" 2>/dev/null | sort

echo ""
echo "MIGRATIONS:"
echo "--------------------------------------"
find app/Database/Migrations -name "*.php" 2>/dev/null | sort

echo ""
echo "SEEDERS:"
echo "--------------------------------------"
find app/Database/Seeds -name "*.php" 2>/dev/null | sort

echo ""
echo "FILTERS:"
echo "--------------------------------------"
find app/Filters -name "*.php" 2>/dev/null | sort

echo ""
echo "HELPERS:"
echo "--------------------------------------"
find app/Helpers -name "*.php" 2>/dev/null | sort

echo ""
echo "LIBRARIES:"
echo "--------------------------------------"
find app/Libraries -name "*.php" 2>/dev/null | sort

echo ""
echo "ENTITIES:"
echo "--------------------------------------"
find app/Entities -name "*.php" 2>/dev/null | sort

echo ""
echo "CONFIG:"
echo "--------------------------------------"
find app/Config -name "*.php" 2>/dev/null | sort

echo ""
echo "======================================"
echo "FILE COUNT SUMMARY:"
echo "======================================"
echo "Controllers: $(find app/Controllers -name "*.php" 2>/dev/null | wc -l)"
echo "Models: $(find app/Models -name "*.php" 2>/dev/null | wc -l)"
echo "Views: $(find app/Views -name "*.php" 2>/dev/null | wc -l)"
echo "Services: $(find app/Services -name "*.php" 2>/dev/null | wc -l)"
echo "Migrations: $(find app/Database/Migrations -name "*.php" 2>/dev/null | wc -l)"
echo "Seeders: $(find app/Database/Seeds -name "*.php" 2>/dev/null | wc -l)"
echo "Filters: $(find app/Filters -name "*.php" 2>/dev/null | wc -l)"
echo "Helpers: $(find app/Helpers -name "*.php" 2>/dev/null | wc -l)"
echo "Libraries: $(find app/Libraries -name "*.php" 2>/dev/null | wc -l)"
echo "Entities: $(find app/Entities -name "*.php" 2>/dev/null | wc -l)"
echo "Config: $(find app/Config -name "*.php" 2>/dev/null | wc -l)"
echo ""
echo "TOTAL PHP FILES: $(find app -name "*.php" 2>/dev/null | wc -l)"
echo "======================================"