<?php
/**
 * Driver Maintenance View
 *
 * Uses the base entity maintenance template.
 * All HTML comes from base template and partials.
 *
 * This file just ensures correct variable names are set.
 */

// Ensure driver variable name matches what base template expects
if (!isset($driver) && isset($entity)) {
    $driver = $entity;
}
if (!isset($isNewDriver) && isset($isNew)) {
    $isNewDriver = $isNew;
}

// Use the base template
echo view('safety/base_entity_maintenance', get_defined_vars());
