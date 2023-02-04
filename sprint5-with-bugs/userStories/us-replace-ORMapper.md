# US5003 - Replace OR-Mapper
Background: Due to security and performance reasons the maintainance of ORMapper Versions 1.2 is 
deprecated. We must replace the version 1.2 with 2.0. 

## User Story: Replace ORMapper 1.2 with ORMapper 2.0

As the data engineer 
I want that the actual ORMapper V1.2 will be replace with ORMapper V2.0
In order to improve the security and performance of the data access. 

ACC1: All data structure are migrated to the new schema of V2.0.
ACC2: Data structure ACCESS is denormalized.
ACC3: A full table scan of reference table REF_ORG_Data (in PERF-ENV-Cloud-098) take < 1,5sec.

# Alternatives:

# Errors: