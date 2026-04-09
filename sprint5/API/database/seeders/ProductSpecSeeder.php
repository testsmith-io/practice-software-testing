<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductSpecSeeder extends Seeder
{
    public function run(): void
    {
        $specs = [
            // Pliers
            'Combination Pliers' => [
                ['spec_name' => 'Weight', 'spec_value' => '340', 'spec_unit' => 'g'],
                ['spec_name' => 'Length', 'spec_value' => '200', 'spec_unit' => 'mm'],
                ['spec_name' => 'Material', 'spec_value' => 'Chrome Vanadium Steel', 'spec_unit' => null],
                ['spec_name' => 'Handle Material', 'spec_value' => 'Bi-component', 'spec_unit' => null],
                ['spec_name' => 'Warranty', 'spec_value' => '2', 'spec_unit' => 'years'],
            ],
            'Pliers' => [
                ['spec_name' => 'Weight', 'spec_value' => '280', 'spec_unit' => 'g'],
                ['spec_name' => 'Length', 'spec_value' => '180', 'spec_unit' => 'mm'],
                ['spec_name' => 'Material', 'spec_value' => 'Carbon Steel', 'spec_unit' => null],
                ['spec_name' => 'Handle Material', 'spec_value' => 'Rubber', 'spec_unit' => null],
                ['spec_name' => 'Warranty', 'spec_value' => '1', 'spec_unit' => 'years'],
            ],
            'Bolt Cutters' => [
                ['spec_name' => 'Weight', 'spec_value' => '2100', 'spec_unit' => 'g'],
                ['spec_name' => 'Length', 'spec_value' => '750', 'spec_unit' => 'mm'],
                ['spec_name' => 'Material', 'spec_value' => 'Hardened Steel', 'spec_unit' => null],
                ['spec_name' => 'Handle Material', 'spec_value' => 'Rubber', 'spec_unit' => null],
                ['spec_name' => 'Max Cutting Capacity', 'spec_value' => '10', 'spec_unit' => 'mm'],
                ['spec_name' => 'Warranty', 'spec_value' => '3', 'spec_unit' => 'years'],
            ],
            'Long Nose Pliers' => [
                ['spec_name' => 'Weight', 'spec_value' => '220', 'spec_unit' => 'g'],
                ['spec_name' => 'Length', 'spec_value' => '170', 'spec_unit' => 'mm'],
                ['spec_name' => 'Material', 'spec_value' => 'Chrome Vanadium Steel', 'spec_unit' => null],
                ['spec_name' => 'Handle Material', 'spec_value' => 'Bi-component', 'spec_unit' => null],
                ['spec_name' => 'Warranty', 'spec_value' => '2', 'spec_unit' => 'years'],
            ],
            'Slip Joint Pliers' => [
                ['spec_name' => 'Weight', 'spec_value' => '300', 'spec_unit' => 'g'],
                ['spec_name' => 'Length', 'spec_value' => '200', 'spec_unit' => 'mm'],
                ['spec_name' => 'Material', 'spec_value' => 'Carbon Steel', 'spec_unit' => null],
                ['spec_name' => 'Handle Material', 'spec_value' => 'PVC', 'spec_unit' => null],
                ['spec_name' => 'Warranty', 'spec_value' => '1', 'spec_unit' => 'years'],
            ],

            // Hammers
            'Claw Hammer with Shock Reduction Grip' => [
                ['spec_name' => 'Weight', 'spec_value' => '567', 'spec_unit' => 'g'],
                ['spec_name' => 'Head Weight', 'spec_value' => '450', 'spec_unit' => 'g'],
                ['spec_name' => 'Length', 'spec_value' => '330', 'spec_unit' => 'mm'],
                ['spec_name' => 'Material', 'spec_value' => 'Carbon Steel', 'spec_unit' => null],
                ['spec_name' => 'Handle Material', 'spec_value' => 'Fiberglass', 'spec_unit' => null],
                ['spec_name' => 'Warranty', 'spec_value' => '5', 'spec_unit' => 'years'],
            ],
            'Hammer' => [
                ['spec_name' => 'Weight', 'spec_value' => '450', 'spec_unit' => 'g'],
                ['spec_name' => 'Head Weight', 'spec_value' => '350', 'spec_unit' => 'g'],
                ['spec_name' => 'Length', 'spec_value' => '300', 'spec_unit' => 'mm'],
                ['spec_name' => 'Material', 'spec_value' => 'Carbon Steel', 'spec_unit' => null],
                ['spec_name' => 'Handle Material', 'spec_value' => 'Wood', 'spec_unit' => null],
                ['spec_name' => 'Warranty', 'spec_value' => '2', 'spec_unit' => 'years'],
            ],
            'Claw Hammer' => [
                ['spec_name' => 'Weight', 'spec_value' => '500', 'spec_unit' => 'g'],
                ['spec_name' => 'Head Weight', 'spec_value' => '400', 'spec_unit' => 'g'],
                ['spec_name' => 'Length', 'spec_value' => '320', 'spec_unit' => 'mm'],
                ['spec_name' => 'Material', 'spec_value' => 'Carbon Steel', 'spec_unit' => null],
                ['spec_name' => 'Handle Material', 'spec_value' => 'Rubber', 'spec_unit' => null],
                ['spec_name' => 'Warranty', 'spec_value' => '3', 'spec_unit' => 'years'],
            ],
            'Thor Hammer' => [
                ['spec_name' => 'Weight', 'spec_value' => '5000', 'spec_unit' => 'g'],
                ['spec_name' => 'Head Weight', 'spec_value' => '4500', 'spec_unit' => 'g'],
                ['spec_name' => 'Length', 'spec_value' => '450', 'spec_unit' => 'mm'],
                ['spec_name' => 'Material', 'spec_value' => 'Uru Metal', 'spec_unit' => null],
                ['spec_name' => 'Handle Material', 'spec_value' => 'Leather Wrapped', 'spec_unit' => null],
                ['spec_name' => 'Warranty', 'spec_value' => 'Lifetime', 'spec_unit' => null],
            ],
            'Sledgehammer' => [
                ['spec_name' => 'Weight', 'spec_value' => '4500', 'spec_unit' => 'g'],
                ['spec_name' => 'Head Weight', 'spec_value' => '3600', 'spec_unit' => 'g'],
                ['spec_name' => 'Length', 'spec_value' => '900', 'spec_unit' => 'mm'],
                ['spec_name' => 'Material', 'spec_value' => 'Forged Steel', 'spec_unit' => null],
                ['spec_name' => 'Handle Material', 'spec_value' => 'Fiberglass', 'spec_unit' => null],
                ['spec_name' => 'Warranty', 'spec_value' => '3', 'spec_unit' => 'years'],
            ],
            'Claw Hammer with Fiberglass Handle' => [
                ['spec_name' => 'Weight', 'spec_value' => '520', 'spec_unit' => 'g'],
                ['spec_name' => 'Head Weight', 'spec_value' => '400', 'spec_unit' => 'g'],
                ['spec_name' => 'Length', 'spec_value' => '340', 'spec_unit' => 'mm'],
                ['spec_name' => 'Material', 'spec_value' => 'Carbon Steel', 'spec_unit' => null],
                ['spec_name' => 'Handle Material', 'spec_value' => 'Fiberglass', 'spec_unit' => null],
                ['spec_name' => 'Warranty', 'spec_value' => '5', 'spec_unit' => 'years'],
            ],
            'Court Hammer' => [
                ['spec_name' => 'Weight', 'spec_value' => '380', 'spec_unit' => 'g'],
                ['spec_name' => 'Head Weight', 'spec_value' => '300', 'spec_unit' => 'g'],
                ['spec_name' => 'Length', 'spec_value' => '280', 'spec_unit' => 'mm'],
                ['spec_name' => 'Material', 'spec_value' => 'Carbon Steel', 'spec_unit' => null],
                ['spec_name' => 'Handle Material', 'spec_value' => 'Wood', 'spec_unit' => null],
                ['spec_name' => 'Warranty', 'spec_value' => '2', 'spec_unit' => 'years'],
            ],

            // Saws
            'Wood Saw' => [
                ['spec_name' => 'Weight', 'spec_value' => '350', 'spec_unit' => 'g'],
                ['spec_name' => 'Blade Length', 'spec_value' => '500', 'spec_unit' => 'mm'],
                ['spec_name' => 'Material', 'spec_value' => 'High Carbon Steel', 'spec_unit' => null],
                ['spec_name' => 'Handle Material', 'spec_value' => 'Wood', 'spec_unit' => null],
                ['spec_name' => 'Teeth Per Inch', 'spec_value' => '7', 'spec_unit' => 'TPI'],
                ['spec_name' => 'Warranty', 'spec_value' => '2', 'spec_unit' => 'years'],
            ],

            // Wrenches
            'Adjustable Wrench' => [
                ['spec_name' => 'Weight', 'spec_value' => '400', 'spec_unit' => 'g'],
                ['spec_name' => 'Length', 'spec_value' => '250', 'spec_unit' => 'mm'],
                ['spec_name' => 'Material', 'spec_value' => 'Chrome Vanadium Steel', 'spec_unit' => null],
                ['spec_name' => 'Max Jaw Opening', 'spec_value' => '30', 'spec_unit' => 'mm'],
                ['spec_name' => 'Warranty', 'spec_value' => '3', 'spec_unit' => 'years'],
            ],
            'Angled Spanner' => [
                ['spec_name' => 'Weight', 'spec_value' => '180', 'spec_unit' => 'g'],
                ['spec_name' => 'Length', 'spec_value' => '200', 'spec_unit' => 'mm'],
                ['spec_name' => 'Material', 'spec_value' => 'Chrome Vanadium Steel', 'spec_unit' => null],
                ['spec_name' => 'Warranty', 'spec_value' => '2', 'spec_unit' => 'years'],
            ],
            'Open-end Spanners (Set)' => [
                ['spec_name' => 'Weight', 'spec_value' => '1200', 'spec_unit' => 'g'],
                ['spec_name' => 'Material', 'spec_value' => 'Chrome Vanadium Steel', 'spec_unit' => null],
                ['spec_name' => 'Pieces', 'spec_value' => '12', 'spec_unit' => 'pcs'],
                ['spec_name' => 'Size Range', 'spec_value' => '6-32', 'spec_unit' => 'mm'],
                ['spec_name' => 'Warranty', 'spec_value' => '5', 'spec_unit' => 'years'],
            ],

            // Screwdrivers
            'Phillips Screwdriver' => [
                ['spec_name' => 'Weight', 'spec_value' => '120', 'spec_unit' => 'g'],
                ['spec_name' => 'Length', 'spec_value' => '240', 'spec_unit' => 'mm'],
                ['spec_name' => 'Material', 'spec_value' => 'Chrome Vanadium Steel', 'spec_unit' => null],
                ['spec_name' => 'Handle Material', 'spec_value' => 'Bi-component', 'spec_unit' => null],
                ['spec_name' => 'Tip Size', 'spec_value' => 'PH2', 'spec_unit' => null],
                ['spec_name' => 'Warranty', 'spec_value' => '2', 'spec_unit' => 'years'],
            ],
            'Mini Screwdriver' => [
                ['spec_name' => 'Weight', 'spec_value' => '45', 'spec_unit' => 'g'],
                ['spec_name' => 'Length', 'spec_value' => '150', 'spec_unit' => 'mm'],
                ['spec_name' => 'Material', 'spec_value' => 'Carbon Steel', 'spec_unit' => null],
                ['spec_name' => 'Handle Material', 'spec_value' => 'Plastic', 'spec_unit' => null],
                ['spec_name' => 'Tip Size', 'spec_value' => 'PH0', 'spec_unit' => null],
                ['spec_name' => 'Warranty', 'spec_value' => '1', 'spec_unit' => 'years'],
            ],

            // Chisels
            'Chisels Set' => [
                ['spec_name' => 'Weight', 'spec_value' => '800', 'spec_unit' => 'g'],
                ['spec_name' => 'Material', 'spec_value' => 'Chrome Vanadium Steel', 'spec_unit' => null],
                ['spec_name' => 'Handle Material', 'spec_value' => 'Wood', 'spec_unit' => null],
                ['spec_name' => 'Pieces', 'spec_value' => '5', 'spec_unit' => 'pcs'],
                ['spec_name' => 'Warranty', 'spec_value' => '2', 'spec_unit' => 'years'],
            ],
            'Wood Carving Chisels' => [
                ['spec_name' => 'Weight', 'spec_value' => '650', 'spec_unit' => 'g'],
                ['spec_name' => 'Material', 'spec_value' => 'High Carbon Steel', 'spec_unit' => null],
                ['spec_name' => 'Handle Material', 'spec_value' => 'Ash Wood', 'spec_unit' => null],
                ['spec_name' => 'Pieces', 'spec_value' => '4', 'spec_unit' => 'pcs'],
                ['spec_name' => 'Warranty', 'spec_value' => '3', 'spec_unit' => 'years'],
            ],
            'Swiss Woodcarving Chisels' => [
                ['spec_name' => 'Weight', 'spec_value' => '550', 'spec_unit' => 'g'],
                ['spec_name' => 'Material', 'spec_value' => 'Swiss Steel', 'spec_unit' => null],
                ['spec_name' => 'Handle Material', 'spec_value' => 'Hornbeam', 'spec_unit' => null],
                ['spec_name' => 'Pieces', 'spec_value' => '6', 'spec_unit' => 'pcs'],
                ['spec_name' => 'Warranty', 'spec_value' => '5', 'spec_unit' => 'years'],
            ],

            // Measures
            'Tape Measure 7.5m' => [
                ['spec_name' => 'Weight', 'spec_value' => '320', 'spec_unit' => 'g'],
                ['spec_name' => 'Length', 'spec_value' => '7.5', 'spec_unit' => 'm'],
                ['spec_name' => 'Blade Width', 'spec_value' => '25', 'spec_unit' => 'mm'],
                ['spec_name' => 'Material', 'spec_value' => 'Nylon Coated Steel', 'spec_unit' => null],
                ['spec_name' => 'Warranty', 'spec_value' => '2', 'spec_unit' => 'years'],
            ],
            'Measuring Tape' => [
                ['spec_name' => 'Weight', 'spec_value' => '250', 'spec_unit' => 'g'],
                ['spec_name' => 'Length', 'spec_value' => '5', 'spec_unit' => 'm'],
                ['spec_name' => 'Blade Width', 'spec_value' => '19', 'spec_unit' => 'mm'],
                ['spec_name' => 'Material', 'spec_value' => 'Steel', 'spec_unit' => null],
                ['spec_name' => 'Warranty', 'spec_value' => '1', 'spec_unit' => 'years'],
            ],
            'Tape Measure 5m' => [
                ['spec_name' => 'Weight', 'spec_value' => '220', 'spec_unit' => 'g'],
                ['spec_name' => 'Length', 'spec_value' => '5', 'spec_unit' => 'm'],
                ['spec_name' => 'Blade Width', 'spec_value' => '19', 'spec_unit' => 'mm'],
                ['spec_name' => 'Material', 'spec_value' => 'Steel', 'spec_unit' => null],
                ['spec_name' => 'Warranty', 'spec_value' => '1', 'spec_unit' => 'years'],
            ],
            'Square Ruler' => [
                ['spec_name' => 'Weight', 'spec_value' => '180', 'spec_unit' => 'g'],
                ['spec_name' => 'Length', 'spec_value' => '300', 'spec_unit' => 'mm'],
                ['spec_name' => 'Material', 'spec_value' => 'Stainless Steel', 'spec_unit' => null],
                ['spec_name' => 'Warranty', 'spec_value' => '3', 'spec_unit' => 'years'],
            ],

            // Safety Gear
            'Safety Goggles' => [
                ['spec_name' => 'Weight', 'spec_value' => '85', 'spec_unit' => 'g'],
                ['spec_name' => 'Material', 'spec_value' => 'Polycarbonate', 'spec_unit' => null],
                ['spec_name' => 'UV Protection', 'spec_value' => 'UV400', 'spec_unit' => null],
                ['spec_name' => 'Anti-fog', 'spec_value' => 'Yes', 'spec_unit' => null],
                ['spec_name' => 'Warranty', 'spec_value' => '1', 'spec_unit' => 'years'],
            ],
            'Safety Helmet Face Shield' => [
                ['spec_name' => 'Weight', 'spec_value' => '450', 'spec_unit' => 'g'],
                ['spec_name' => 'Material', 'spec_value' => 'ABS Plastic', 'spec_unit' => null],
                ['spec_name' => 'Shield Material', 'spec_value' => 'Polycarbonate', 'spec_unit' => null],
                ['spec_name' => 'Adjustable', 'spec_value' => 'Yes', 'spec_unit' => null],
                ['spec_name' => 'Warranty', 'spec_value' => '2', 'spec_unit' => 'years'],
            ],
            'Protective Gloves' => [
                ['spec_name' => 'Weight', 'spec_value' => '120', 'spec_unit' => 'g'],
                ['spec_name' => 'Material', 'spec_value' => 'Leather', 'spec_unit' => null],
                ['spec_name' => 'Size', 'spec_value' => 'L', 'spec_unit' => null],
                ['spec_name' => 'Cut Resistance', 'spec_value' => 'Level 3', 'spec_unit' => null],
                ['spec_name' => 'Warranty', 'spec_value' => '1', 'spec_unit' => 'years'],
            ],
            'Super-thin Protection Gloves' => [
                ['spec_name' => 'Weight', 'spec_value' => '60', 'spec_unit' => 'g'],
                ['spec_name' => 'Material', 'spec_value' => 'Nitrile', 'spec_unit' => null],
                ['spec_name' => 'Size', 'spec_value' => 'M', 'spec_unit' => null],
                ['spec_name' => 'Pieces', 'spec_value' => '50', 'spec_unit' => 'pcs'],
                ['spec_name' => 'Warranty', 'spec_value' => '1', 'spec_unit' => 'years'],
            ],
            'Construction Helmet' => [
                ['spec_name' => 'Weight', 'spec_value' => '350', 'spec_unit' => 'g'],
                ['spec_name' => 'Material', 'spec_value' => 'HDPE', 'spec_unit' => null],
                ['spec_name' => 'Adjustable', 'spec_value' => 'Yes', 'spec_unit' => null],
                ['spec_name' => 'Ventilation', 'spec_value' => 'Yes', 'spec_unit' => null],
                ['spec_name' => 'Warranty', 'spec_value' => '3', 'spec_unit' => 'years'],
            ],
            'Ear Protection' => [
                ['spec_name' => 'Weight', 'spec_value' => '200', 'spec_unit' => 'g'],
                ['spec_name' => 'Material', 'spec_value' => 'ABS Plastic', 'spec_unit' => null],
                ['spec_name' => 'Noise Reduction', 'spec_value' => '28', 'spec_unit' => 'dB'],
                ['spec_name' => 'Adjustable', 'spec_value' => 'Yes', 'spec_unit' => null],
                ['spec_name' => 'Warranty', 'spec_value' => '2', 'spec_unit' => 'years'],
            ],

            // Fasteners
            'Screws' => [
                ['spec_name' => 'Material', 'spec_value' => 'Zinc Plated Steel', 'spec_unit' => null],
                ['spec_name' => 'Pieces', 'spec_value' => '100', 'spec_unit' => 'pcs'],
                ['spec_name' => 'Size', 'spec_value' => 'M5x30', 'spec_unit' => null],
                ['spec_name' => 'Head Type', 'spec_value' => 'Pan Head', 'spec_unit' => null],
            ],
            'Nuts and bolts' => [
                ['spec_name' => 'Material', 'spec_value' => 'Stainless Steel', 'spec_unit' => null],
                ['spec_name' => 'Pieces', 'spec_value' => '200', 'spec_unit' => 'pcs'],
                ['spec_name' => 'Size Range', 'spec_value' => 'M3-M10', 'spec_unit' => null],
            ],
            'Cross-head screws' => [
                ['spec_name' => 'Material', 'spec_value' => 'Zinc Plated Steel', 'spec_unit' => null],
                ['spec_name' => 'Pieces', 'spec_value' => '50', 'spec_unit' => 'pcs'],
                ['spec_name' => 'Size', 'spec_value' => 'M4x25', 'spec_unit' => null],
                ['spec_name' => 'Head Type', 'spec_value' => 'Phillips', 'spec_unit' => null],
            ],
            'Flat-Head Wood Screws' => [
                ['spec_name' => 'Material', 'spec_value' => 'Yellow Zinc Steel', 'spec_unit' => null],
                ['spec_name' => 'Pieces', 'spec_value' => '75', 'spec_unit' => 'pcs'],
                ['spec_name' => 'Size', 'spec_value' => '4x40', 'spec_unit' => null],
                ['spec_name' => 'Head Type', 'spec_value' => 'Flat Head', 'spec_unit' => null],
            ],
            'M4 Nuts' => [
                ['spec_name' => 'Material', 'spec_value' => 'Stainless Steel', 'spec_unit' => null],
                ['spec_name' => 'Pieces', 'spec_value' => '100', 'spec_unit' => 'pcs'],
                ['spec_name' => 'Size', 'spec_value' => 'M4', 'spec_unit' => null],
            ],
            'Washers' => [
                ['spec_name' => 'Material', 'spec_value' => 'Zinc Plated Steel', 'spec_unit' => null],
                ['spec_name' => 'Pieces', 'spec_value' => '150', 'spec_unit' => 'pcs'],
                ['spec_name' => 'Size Range', 'spec_value' => 'M4-M10', 'spec_unit' => null],
            ],

            // Storage
            'Drawer Tool Cabinet' => [
                ['spec_name' => 'Weight', 'spec_value' => '25000', 'spec_unit' => 'g'],
                ['spec_name' => 'Material', 'spec_value' => 'Cold Rolled Steel', 'spec_unit' => null],
                ['spec_name' => 'Drawers', 'spec_value' => '5', 'spec_unit' => 'pcs'],
                ['spec_name' => 'Dimensions', 'spec_value' => '680x460x1000', 'spec_unit' => 'mm'],
                ['spec_name' => 'Load Capacity', 'spec_value' => '200', 'spec_unit' => 'kg'],
                ['spec_name' => 'Warranty', 'spec_value' => '5', 'spec_unit' => 'years'],
            ],
            'Tool Cabinet' => [
                ['spec_name' => 'Weight', 'spec_value' => '18000', 'spec_unit' => 'g'],
                ['spec_name' => 'Material', 'spec_value' => 'Steel', 'spec_unit' => null],
                ['spec_name' => 'Drawers', 'spec_value' => '3', 'spec_unit' => 'pcs'],
                ['spec_name' => 'Dimensions', 'spec_value' => '600x400x800', 'spec_unit' => 'mm'],
                ['spec_name' => 'Load Capacity', 'spec_value' => '150', 'spec_unit' => 'kg'],
                ['spec_name' => 'Warranty', 'spec_value' => '3', 'spec_unit' => 'years'],
            ],

            // Workbenches
            'Workbench with Drawers' => [
                ['spec_name' => 'Weight', 'spec_value' => '45000', 'spec_unit' => 'g'],
                ['spec_name' => 'Material', 'spec_value' => 'Hardwood / Steel', 'spec_unit' => null],
                ['spec_name' => 'Drawers', 'spec_value' => '4', 'spec_unit' => 'pcs'],
                ['spec_name' => 'Dimensions', 'spec_value' => '1500x600x850', 'spec_unit' => 'mm'],
                ['spec_name' => 'Load Capacity', 'spec_value' => '500', 'spec_unit' => 'kg'],
                ['spec_name' => 'Warranty', 'spec_value' => '5', 'spec_unit' => 'years'],
            ],
            'Wooden Workbench' => [
                ['spec_name' => 'Weight', 'spec_value' => '35000', 'spec_unit' => 'g'],
                ['spec_name' => 'Material', 'spec_value' => 'Beech Wood', 'spec_unit' => null],
                ['spec_name' => 'Dimensions', 'spec_value' => '1400x500x850', 'spec_unit' => 'mm'],
                ['spec_name' => 'Load Capacity', 'spec_value' => '300', 'spec_unit' => 'kg'],
                ['spec_name' => 'Warranty', 'spec_value' => '5', 'spec_unit' => 'years'],
            ],

            // Tool Belt
            'Leather toolbelt' => [
                ['spec_name' => 'Weight', 'spec_value' => '650', 'spec_unit' => 'g'],
                ['spec_name' => 'Material', 'spec_value' => 'Full Grain Leather', 'spec_unit' => null],
                ['spec_name' => 'Pockets', 'spec_value' => '11', 'spec_unit' => 'pcs'],
                ['spec_name' => 'Adjustable', 'spec_value' => 'Yes', 'spec_unit' => null],
                ['spec_name' => 'Warranty', 'spec_value' => '3', 'spec_unit' => 'years'],
            ],

            // Power Tools - Sanders
            'Sheet Sander' => [
                ['spec_name' => 'Weight', 'spec_value' => '1800', 'spec_unit' => 'g'],
                ['spec_name' => 'Power', 'spec_value' => '240', 'spec_unit' => 'W'],
                ['spec_name' => 'Voltage', 'spec_value' => '220', 'spec_unit' => 'V'],
                ['spec_name' => 'Vibration Rate', 'spec_value' => '12000', 'spec_unit' => 'rpm'],
                ['spec_name' => 'Pad Size', 'spec_value' => '112x102', 'spec_unit' => 'mm'],
                ['spec_name' => 'Warranty', 'spec_value' => '2', 'spec_unit' => 'years'],
            ],
            'Belt Sander' => [
                ['spec_name' => 'Weight', 'spec_value' => '3200', 'spec_unit' => 'g'],
                ['spec_name' => 'Power', 'spec_value' => '800', 'spec_unit' => 'W'],
                ['spec_name' => 'Voltage', 'spec_value' => '220', 'spec_unit' => 'V'],
                ['spec_name' => 'Belt Speed', 'spec_value' => '380', 'spec_unit' => 'm/min'],
                ['spec_name' => 'Belt Size', 'spec_value' => '76x533', 'spec_unit' => 'mm'],
                ['spec_name' => 'Warranty', 'spec_value' => '2', 'spec_unit' => 'years'],
            ],
            'Random Orbit Sander' => [
                ['spec_name' => 'Weight', 'spec_value' => '1500', 'spec_unit' => 'g'],
                ['spec_name' => 'Power', 'spec_value' => '300', 'spec_unit' => 'W'],
                ['spec_name' => 'Voltage', 'spec_value' => '220', 'spec_unit' => 'V'],
                ['spec_name' => 'Vibration Rate', 'spec_value' => '14000', 'spec_unit' => 'rpm'],
                ['spec_name' => 'Pad Diameter', 'spec_value' => '125', 'spec_unit' => 'mm'],
                ['spec_name' => 'Warranty', 'spec_value' => '2', 'spec_unit' => 'years'],
            ],

            // Circular Saw
            'Circular Saw' => [
                ['spec_name' => 'Weight', 'spec_value' => '4200', 'spec_unit' => 'g'],
                ['spec_name' => 'Power', 'spec_value' => '1400', 'spec_unit' => 'W'],
                ['spec_name' => 'Voltage', 'spec_value' => '220', 'spec_unit' => 'V'],
                ['spec_name' => 'Blade Diameter', 'spec_value' => '190', 'spec_unit' => 'mm'],
                ['spec_name' => 'Max Cut Depth', 'spec_value' => '67', 'spec_unit' => 'mm'],
                ['spec_name' => 'No Load Speed', 'spec_value' => '5500', 'spec_unit' => 'rpm'],
                ['spec_name' => 'Warranty', 'spec_value' => '2', 'spec_unit' => 'years'],
            ],

            // Cordless Drills
            'Cordless Drill 20V' => [
                ['spec_name' => 'Weight', 'spec_value' => '1800', 'spec_unit' => 'g'],
                ['spec_name' => 'Voltage', 'spec_value' => '20', 'spec_unit' => 'V'],
                ['spec_name' => 'Battery', 'spec_value' => '4.0', 'spec_unit' => 'Ah'],
                ['spec_name' => 'Chuck Size', 'spec_value' => '13', 'spec_unit' => 'mm'],
                ['spec_name' => 'Max Torque', 'spec_value' => '60', 'spec_unit' => 'Nm'],
                ['spec_name' => 'Speed', 'spec_value' => '1800', 'spec_unit' => 'rpm'],
                ['spec_name' => 'Warranty', 'spec_value' => '3', 'spec_unit' => 'years'],
            ],
            'Cordless Drill 24V' => [
                ['spec_name' => 'Weight', 'spec_value' => '2100', 'spec_unit' => 'g'],
                ['spec_name' => 'Voltage', 'spec_value' => '24', 'spec_unit' => 'V'],
                ['spec_name' => 'Battery', 'spec_value' => '5.0', 'spec_unit' => 'Ah'],
                ['spec_name' => 'Chuck Size', 'spec_value' => '13', 'spec_unit' => 'mm'],
                ['spec_name' => 'Max Torque', 'spec_value' => '80', 'spec_unit' => 'Nm'],
                ['spec_name' => 'Speed', 'spec_value' => '2000', 'spec_unit' => 'rpm'],
                ['spec_name' => 'Warranty', 'spec_value' => '3', 'spec_unit' => 'years'],
            ],
            'Cordless Drill 18V' => [
                ['spec_name' => 'Weight', 'spec_value' => '1600', 'spec_unit' => 'g'],
                ['spec_name' => 'Voltage', 'spec_value' => '18', 'spec_unit' => 'V'],
                ['spec_name' => 'Battery', 'spec_value' => '3.0', 'spec_unit' => 'Ah'],
                ['spec_name' => 'Chuck Size', 'spec_value' => '10', 'spec_unit' => 'mm'],
                ['spec_name' => 'Max Torque', 'spec_value' => '50', 'spec_unit' => 'Nm'],
                ['spec_name' => 'Speed', 'spec_value' => '1500', 'spec_unit' => 'rpm'],
                ['spec_name' => 'Warranty', 'spec_value' => '2', 'spec_unit' => 'years'],
            ],
            'Cordless Drill 12V' => [
                ['spec_name' => 'Weight', 'spec_value' => '1200', 'spec_unit' => 'g'],
                ['spec_name' => 'Voltage', 'spec_value' => '12', 'spec_unit' => 'V'],
                ['spec_name' => 'Battery', 'spec_value' => '2.0', 'spec_unit' => 'Ah'],
                ['spec_name' => 'Chuck Size', 'spec_value' => '10', 'spec_unit' => 'mm'],
                ['spec_name' => 'Max Torque', 'spec_value' => '30', 'spec_unit' => 'Nm'],
                ['spec_name' => 'Speed', 'spec_value' => '1200', 'spec_unit' => 'rpm'],
                ['spec_name' => 'Warranty', 'spec_value' => '2', 'spec_unit' => 'years'],
            ],
        ];

        foreach ($specs as $productName => $productSpecs) {
            $product = DB::table('products')->where('name', '=', $productName)->first();
            if (!$product) {
                continue;
            }

            $rows = [];
            foreach ($productSpecs as $spec) {
                $rows[] = [
                    'id' => Str::ulid()->toBase32(),
                    'product_id' => $product->id,
                    'spec_name' => $spec['spec_name'],
                    'spec_value' => $spec['spec_value'],
                    'spec_unit' => $spec['spec_unit'],
                ];
            }
            DB::table('product_specs')->insert($rows);
        }
    }
}
