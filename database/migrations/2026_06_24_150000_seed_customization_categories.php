<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Clear existing assignments and categories for a clean slate.
        DB::table('asset_customization_categories')->delete();
        DB::table('customization_categories')->delete();

        $categories = [
            'Architectural Design',
            'BOQ (Bill of Quantities)',
            'Structural Design',
            'Material & Labour Schedule',
            'Interior Design',
            'Mechanical & Plumbing Design',
            'Electrical Design',
            'Customized Furniture Design',
            'Modular Kitchen Design',
            'Wardrobe & Storage Design',
            'Bathroom Vanity & Sanitary Layout Design',
            'Lighting Layout & Fixture Design',
            'Ceiling & False Ceiling Design',
            'Wall Paneling & Cladding Design',
            'Joinery & Millwork Drawings',
            'Construction Method Statement',
            'Pricing and Tendering',
            'Construction Phasing Plan',
            'Site Execution Drawings',
            'Shop Drawings',
            'As-Built Drawings',
            'Value Engineering Optimization',
            'Construction Cost Optimization',
            'Contractor Coordination Drawings',
            'Valuation and Variation Costing',
            'Quantity Surveying',
            'BIM Model Creation (LOD 200–500)',
            'Revit Family Creation',
            'Clash Detection & Coordination',
            '4D Construction Sequencing',
            '5D Cost Integration',
            'IFC / COBie Exports',
            'Digital Twin Setup',
            'Landscape Design',
            'Hardscape & Paving Design',
            'Boundary Wall & Gate Design',
            'Outdoor Lighting Design',
            'Water Features & Fountain Design',
            'Garden Irrigation Design',
            'Green Building Design',
            'Energy Efficiency Optimization',
            'Solar PV System Design',
            'Rainwater Harvesting Design',
            'Greywater Recycling Design',
            'Daylight & Ventilation Analysis',
            'Carbon Footprint Assessment',
            'Fire Fighting & Life Safety Design',
            'Local Code Compliance Review',
            'Accessibility Design (Universal Design)',
            'Seismic & Wind Load Review',
            'Authority Approval Drawings',
            'Permit & Submission Drawings',
            'Project Scheduling (Primavera / MS Project)',
            'Cost Control & Cash Flow Forecast',
            'Tender Documentation',
            'Bid Evaluation Reports',
            'Contractor Selection Support',
            '3D Exterior Visualization',
            '3D Interior Visualization',
            'Walkthrough Animation',
            'Virtual Reality (VR) Tour',
            'Augmented Reality (AR) Model',
            'Marketing Brochures & Layouts',
            'Sales Presentation Decks',
            'Smart Home Automation Design',
            'IoT Integration Planning',
            'Security & Surveillance System Design',
            'Access Control & Intercom Design',
            'AV & Home Theater Design',
            'Feasibility Study',
            'Site Analysis & Zoning Study',
            'Concept Validation',
            'Design Due Diligence',
            'Development Advisory',
        ];

        $now = now();
        $rows = array_map(fn ($name) => [
            'name' => $name,
            'description' => $name,
            'created_at' => $now,
            'updated_at' => $now,
        ], $categories);

        DB::table('customization_categories')->insert($rows);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('asset_customization_categories')->delete();
        DB::table('customization_categories')->delete();
    }
};
