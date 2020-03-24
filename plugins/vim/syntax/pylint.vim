" These file contents should be put in .vim/syntax for the file types you wish to highlight Phan issue names in (e.g. ~/.vim/syntax/text.vim)
" The highlighted issue types don't include issue types emitted by plugins
" (generated by internal/dump_vim_highlighting.php)
syntax keyword phanCritical PhanAccessClassConstantPrivate PhanAccessClassConstantProtected PhanAccessExtendsFinalClass PhanAccessExtendsFinalClassInternal PhanAccessMethodPrivate PhanAccessMethodPrivateWithCallMagicMethod PhanAccessMethodProtected PhanAccessMethodProtectedWithCallMagicMethod PhanAccessNonStaticToStatic PhanAccessNonStaticToStaticProperty PhanAccessOverridesFinalMethod PhanAccessOverridesFinalMethodInternal PhanAccessOverridesFinalMethodPHPDoc PhanAccessPropertyNonStaticAsStatic PhanAccessPropertyPrivate PhanAccessPropertyProtected PhanAccessPropertyStaticAsNonStatic PhanAccessStaticToNonStatic PhanAccessStaticToNonStaticProperty PhanAccessWrongInheritanceCategory PhanAccessWrongInheritanceCategoryInternal PhanClassContainsAbstractMethod PhanClassContainsAbstractMethodInternal PhanCompatibleIterableTypePHP70 PhanCompatibleKeyedArrayAssignPHP70 PhanCompatibleMultiExceptionCatchPHP70 PhanCompatibleNullableTypePHP70 PhanCompatibleObjectTypePHP71 PhanCompatibleShortArrayAssignPHP70 PhanCompatibleUseIterablePHP71 PhanCompatibleUseObjectPHP71 PhanCompatibleUseVoidPHP70 PhanCompatibleVoidTypePHP70 PhanConstantAccessSignatureMismatch PhanConstantAccessSignatureMismatchInternal PhanContextNotObject PhanContinueOrBreakNotInLoop PhanContinueOrBreakTooManyLevels PhanDuplicateUseConstant PhanDuplicateUseFunction PhanDuplicateUseNormal PhanInfiniteLoop PhanInvalidConstantExpression PhanInvalidNode PhanInvalidRequireFile PhanInvalidTraitUse PhanInvalidWriteToTemporaryExpression PhanMissingRequireFile PhanNonClassMethodCall PhanParamMustBeUserDefinedClassname PhanParamRedefined PhanParamSignatureRealMismatchHasParamType PhanParamSignatureRealMismatchReturnType PhanParamSignatureRealMismatchReturnTypeInternal PhanParamTooFew PhanParamTooFewCallable PhanParamTooFewInternal PhanParentlessClass PhanPossiblyUndeclaredMethod PhanPropertyAccessSignatureMismatch PhanPropertyAccessSignatureMismatchInternal PhanRedefineClassConstant PhanRedefineProperty PhanSyntaxEmptyListArrayDestructuring PhanSyntaxError PhanSyntaxMixedKeyNoKeyArrayDestructuring PhanSyntaxReturnExpectedValue PhanSyntaxReturnValueInVoid PhanTypeExpectedObjectPropAccess PhanTypeInvalidCallableMethodName PhanTypeInvalidCloneNotObject PhanTypeInvalidEval PhanTypeInvalidMethodName PhanTypeInvalidPropertyDefaultReal PhanTypeInvalidRequire PhanTypeInvalidStaticMethodName PhanTypeInvalidStaticPropertyName PhanTypeInvalidTraitParam PhanTypeInvalidTraitReturn PhanTypeInvalidYieldFrom PhanTypeMismatchArgumentInternalReal PhanTypeMismatchArgumentReal PhanTypeMismatchDimEmpty PhanTypeMismatchPropertyReal PhanTypeMismatchReturnReal PhanTypeMissingReturnReal PhanUndeclaredAliasedMethodOfTrait PhanUndeclaredClass PhanUndeclaredClassAliasOriginal PhanUndeclaredClassCatch PhanUndeclaredClassConstant PhanUndeclaredClassInstanceof PhanUndeclaredClassMethod PhanUndeclaredClassStaticProperty PhanUndeclaredConstantOfClass PhanUndeclaredExtendedClass PhanUndeclaredFunction PhanUndeclaredInterface PhanUndeclaredMethod PhanUndeclaredStaticMethod PhanUndeclaredStaticProperty PhanUndeclaredThis PhanUndeclaredTrait
syntax keyword phanNormal PhanAccessClassConstantInternal PhanAccessClassInternal PhanAccessConstantInternal PhanAccessMethodInternal PhanAccessOverridesFinalMethodInTrait PhanAccessOwnConstructor PhanAccessPropertyInternal PhanAccessReadOnlyMagicProperty PhanAccessSignatureMismatch PhanAccessSignatureMismatchInternal PhanAccessWriteOnlyMagicProperty PhanAmbiguousTraitAliasSource PhanCompatibleAnyReturnTypePHP56 PhanCompatibleAutoload PhanCompatibleDefaultEqualsNull PhanCompatibleDimAlternativeSyntax PhanCompatibleExpressionPHP7 PhanCompatibleImplodeOrder PhanCompatibleNegativeStringOffset PhanCompatiblePHP7 PhanCompatiblePHP8PHP4Constructor PhanCompatibleScalarTypePHP56 PhanCompatibleStaticType PhanCompatibleSyntaxNotice PhanCompatibleTypedProperty PhanCompatibleUnionType PhanCompatibleUnparenthesizedTernary PhanCompatibleUnsetCast PhanConstructAccessSignatureMismatch PhanContextNotObjectInCallable PhanContextNotObjectUsingSelf PhanContinueTargetingSwitch PhanDeprecatedCaseInsensitiveDefine PhanDeprecatedClass PhanDeprecatedClassConstant PhanDeprecatedFunction PhanDeprecatedFunctionInternal PhanDeprecatedInterface PhanDeprecatedProperty PhanDeprecatedTrait PhanDivisionByZero PhanEmptyFQSENInCallable PhanEmptyFQSENInClasslike PhanGenericConstructorTypes PhanGenericGlobalVariable PhanIncompatibleCompositionMethod PhanIncompatibleCompositionProp PhanInfiniteRecursion PhanInvalidConstantFQSEN PhanInvalidFQSENInCallable PhanInvalidFQSENInClasslike PhanModuloByZero PhanNoopNewNoSideEffects PhanParamSignatureMismatch PhanParamSignatureMismatchInternal PhanParamSignatureRealMismatchHasNoParamType PhanParamSignatureRealMismatchHasNoParamTypeInternal PhanParamSignatureRealMismatchHasParamTypeInternal PhanParamSignatureRealMismatchParamIsNotReference PhanParamSignatureRealMismatchParamIsNotReferenceInternal PhanParamSignatureRealMismatchParamIsReference PhanParamSignatureRealMismatchParamIsReferenceInternal PhanParamSignatureRealMismatchParamNotVariadic PhanParamSignatureRealMismatchParamNotVariadicInternal PhanParamSignatureRealMismatchParamType PhanParamSignatureRealMismatchParamTypeInternal PhanParamSignatureRealMismatchParamVariadic PhanParamSignatureRealMismatchParamVariadicInternal PhanParamSignatureRealMismatchTooFewParameters PhanParamSignatureRealMismatchTooFewParametersInternal PhanParamSignatureRealMismatchTooManyRequiredParameters PhanParamSignatureRealMismatchTooManyRequiredParametersInternal PhanParamSpecial1 PhanParamSpecial2 PhanParamSpecial3 PhanParamSpecial4 PhanParamSuspiciousOrder PhanParamTypeMismatch PhanPartialTypeMismatchArgument PhanPartialTypeMismatchArgumentInternal PhanPartialTypeMismatchProperty PhanPartialTypeMismatchReturn PhanPossiblyFalseTypeArgument PhanPossiblyFalseTypeArgumentInternal PhanPossiblyFalseTypeMismatchProperty PhanPossiblyFalseTypeReturn PhanPossiblyInfiniteRecursionSameParams PhanPossiblyNonClassMethodCall PhanPossiblyNullTypeArgument PhanPossiblyNullTypeArgumentInternal PhanPossiblyNullTypeMismatchProperty PhanPossiblyNullTypeReturn PhanPossiblyUndeclaredGlobalVariable PhanPossiblyUndeclaredProperty PhanPossiblyUndeclaredVariable PhanReadOnlyPHPDocProperty PhanReadOnlyPrivateProperty PhanReadOnlyProtectedProperty PhanReadOnlyPublicProperty PhanRedefineClass PhanRedefineClassAlias PhanRedefineClassInternal PhanRedefineFunction PhanRedefineFunctionInternal PhanRedefinedClassReference PhanRedefinedExtendedClass PhanRedefinedInheritedInterface PhanRedefinedUsedTrait PhanRedundantArrayValuesCall PhanRelativePathUsed PhanRequiredTraitNotAdded PhanReservedConstantName PhanStaticCallToNonStatic PhanSuspiciousMagicConstant PhanSyntaxCompileWarning PhanTemplateTypeConstant PhanTemplateTypeNotDeclaredInFunctionParams PhanTemplateTypeNotUsedInFunctionReturn PhanTemplateTypeStaticMethod PhanTemplateTypeStaticProperty PhanThrowCommentInToString PhanThrowStatementInToString PhanTypeArrayOperator PhanTypeArraySuspicious PhanTypeArraySuspiciousNull PhanTypeArraySuspiciousNullable PhanTypeArrayUnsetSuspicious PhanTypeComparisonToInvalidClass PhanTypeComparisonToInvalidClassType PhanTypeErrorInOperation PhanTypeExpectedObject PhanTypeExpectedObjectOrClassName PhanTypeExpectedObjectPropAccessButGotNull PhanTypeExpectedObjectStaticPropAccess PhanTypeInstantiateAbstract PhanTypeInstantiateInterface PhanTypeInstantiateTrait PhanTypeInvalidBitwiseBinaryOperator PhanTypeInvalidCallExpressionAssignment PhanTypeInvalidCallable PhanTypeInvalidCallableArrayKey PhanTypeInvalidCallableArraySize PhanTypeInvalidCallableObjectOfMethod PhanTypeInvalidClosureScope PhanTypeInvalidDimOffset PhanTypeInvalidDimOffsetArrayDestructuring PhanTypeInvalidExpressionArrayDestructuring PhanTypeInvalidInstanceof PhanTypeInvalidLeftOperand PhanTypeInvalidLeftOperandOfAdd PhanTypeInvalidLeftOperandOfIntegerOp PhanTypeInvalidLeftOperandOfNumericOp PhanTypeInvalidPropertyName PhanTypeInvalidRightOperand PhanTypeInvalidRightOperandOfAdd PhanTypeInvalidRightOperandOfIntegerOp PhanTypeInvalidRightOperandOfNumericOp PhanTypeInvalidThrowsIsInterface PhanTypeInvalidThrowsIsTrait PhanTypeInvalidThrowsNonObject PhanTypeInvalidThrowsNonThrowable PhanTypeInvalidUnaryOperandBitwiseNot PhanTypeInvalidUnaryOperandIncOrDec PhanTypeInvalidUnaryOperandNumeric PhanTypeMismatchArgument PhanTypeMismatchArgumentInternal PhanTypeMismatchArgumentInternalProbablyReal PhanTypeMismatchArgumentPropertyReference PhanTypeMismatchArgumentPropertyReferenceReal PhanTypeMismatchArrayDestructuringKey PhanTypeMismatchBitwiseBinaryOperands PhanTypeMismatchDeclaredParam PhanTypeMismatchDeclaredParamNullable PhanTypeMismatchDeclaredReturn PhanTypeMismatchDeclaredReturnNullable PhanTypeMismatchDefault PhanTypeMismatchDimAssignment PhanTypeMismatchDimFetch PhanTypeMismatchDimFetchNullable PhanTypeMismatchForeach PhanTypeMismatchGeneratorYieldKey PhanTypeMismatchGeneratorYieldValue PhanTypeMismatchProperty PhanTypeMismatchPropertyRealByRef PhanTypeMismatchReturn PhanTypeMismatchReturnNullable PhanTypeMismatchUnpackKey PhanTypeMismatchUnpackKeyArraySpread PhanTypeMismatchUnpackValue PhanTypeMissingReturn PhanTypeNoAccessiblePropertiesForeach PhanTypeNoPropertiesForeach PhanTypeNonVarPassByRef PhanTypeNonVarReturnByRef PhanTypeParentConstructorCalled PhanTypePossiblyInvalidCallable PhanTypePossiblyInvalidDimOffset PhanTypeSuspiciousEcho PhanTypeSuspiciousIndirectVariable PhanTypeSuspiciousNonTraversableForeach PhanTypeSuspiciousStringExpression PhanUndeclaredClassInCallable PhanUndeclaredClassProperty PhanUndeclaredClassReference PhanUndeclaredClosureScope PhanUndeclaredConstant PhanUndeclaredFunctionInCallable PhanUndeclaredGlobalVariable PhanUndeclaredMethodInCallable PhanUndeclaredProperty PhanUndeclaredStaticMethodInCallable PhanUndeclaredTypeParameter PhanUndeclaredTypeProperty PhanUndeclaredTypeReturnType PhanUndeclaredTypeThrowsType PhanUndeclaredVariable PhanUnreachableCatch PhanUnreferencedClass PhanUnreferencedClosure PhanUnreferencedConstant PhanUnreferencedFunction PhanUnreferencedPHPDocProperty PhanUnreferencedPrivateClassConstant PhanUnreferencedPrivateMethod PhanUnreferencedPrivateProperty PhanUnreferencedProtectedClassConstant PhanUnreferencedProtectedMethod PhanUnreferencedProtectedProperty PhanUnreferencedPublicClassConstant PhanUnreferencedPublicMethod PhanUnreferencedPublicProperty PhanUnreferencedUseConstant PhanUnreferencedUseFunction PhanUnreferencedUseNormal PhanUnusedClosureParameter PhanUnusedClosureUseVariable PhanUnusedGlobalFunctionParameter PhanUnusedPrivateFinalMethodParameter PhanUnusedPrivateMethodParameter PhanUnusedProtectedFinalMethodParameter PhanUnusedProtectedMethodParameter PhanUnusedProtectedNoOverrideMethodParameter PhanUnusedPublicFinalMethodParameter PhanUnusedPublicMethodParameter PhanUnusedPublicNoOverrideMethodParameter PhanUnusedReturnBranchWithoutSideEffects PhanUnusedVariable PhanUnusedVariableGlobal PhanUnusedVariableReference PhanUnusedVariableStatic PhanUseConstantNoEffect PhanUseFunctionNoEffect PhanUseNormalNoEffect PhanVariableUseClause PhanWriteOnlyPHPDocProperty PhanWriteOnlyPrivateProperty PhanWriteOnlyProtectedProperty PhanWriteOnlyPublicProperty
syntax keyword phanLow PhanAccessReadOnlyProperty PhanAccessWriteOnlyProperty PhanCoalescingAlwaysNull PhanCoalescingAlwaysNullInGlobalScope PhanCoalescingAlwaysNullInLoop PhanCoalescingNeverNull PhanCoalescingNeverNullInGlobalScope PhanCoalescingNeverNullInLoop PhanCommentAmbiguousClosure PhanCommentDuplicateMagicMethod PhanCommentDuplicateMagicProperty PhanCommentDuplicateParam PhanCommentOverrideOnNonOverrideConstant PhanCommentOverrideOnNonOverrideMethod PhanCommentParamAssertionWithoutRealParam PhanCommentParamOnEmptyParamList PhanCommentParamOutOfOrder PhanCommentParamWithoutRealParam PhanDebugAnnotation PhanEmptyClosure PhanEmptyFile PhanEmptyForeach PhanEmptyForeachBody PhanEmptyFunction PhanEmptyPrivateMethod PhanEmptyProtectedMethod PhanEmptyPublicMethod PhanEmptyYieldFrom PhanImpossibleCondition PhanImpossibleConditionInGlobalScope PhanImpossibleConditionInLoop PhanImpossibleTypeComparison PhanImpossibleTypeComparisonInGlobalScope PhanImpossibleTypeComparisonInLoop PhanInvalidCommentForDeclarationType PhanInvalidMixin PhanMismatchVariadicComment PhanMismatchVariadicParam PhanMisspelledAnnotation PhanNoopArray PhanNoopArrayAccess PhanNoopBinaryOperator PhanNoopCast PhanNoopClosure PhanNoopConstant PhanNoopEmpty PhanNoopEncapsulatedStringLiteral PhanNoopIsset PhanNoopNew PhanNoopNumericLiteral PhanNoopProperty PhanNoopStringLiteral PhanNoopTernary PhanNoopUnaryOperator PhanNoopVariable PhanParamReqAfterOpt PhanParamSignaturePHPDocMismatchHasNoParamType PhanParamSignaturePHPDocMismatchHasParamType PhanParamSignaturePHPDocMismatchParamIsNotReference PhanParamSignaturePHPDocMismatchParamIsReference PhanParamSignaturePHPDocMismatchParamNotVariadic PhanParamSignaturePHPDocMismatchParamType PhanParamSignaturePHPDocMismatchParamVariadic PhanParamSignaturePHPDocMismatchReturnType PhanParamSignaturePHPDocMismatchTooFewParameters PhanParamSignaturePHPDocMismatchTooManyRequiredParameters PhanParamTooMany PhanParamTooManyCallable PhanParamTooManyInternal PhanParamTooManyUnpack PhanParamTooManyUnpackInternal PhanPossiblyUnsetPropertyOfThis PhanPowerOfZero PhanRedundantCondition PhanRedundantConditionInGlobalScope PhanRedundantConditionInLoop PhanShadowedVariableInArrowFunc PhanStaticPropIsStaticType PhanSuspiciousBinaryAddLists PhanSuspiciousLoopDirection PhanSuspiciousTruthyCondition PhanSuspiciousTruthyString PhanSuspiciousValueComparison PhanSuspiciousValueComparisonInGlobalScope PhanSuspiciousValueComparisonInLoop PhanSuspiciousWeakTypeComparison PhanSuspiciousWeakTypeComparisonInGlobalScope PhanSuspiciousWeakTypeComparisonInLoop PhanThrowTypeAbsent PhanThrowTypeAbsentForCall PhanThrowTypeMismatch PhanThrowTypeMismatchForCall PhanTraitParentReference PhanTypeComparisonFromArray PhanTypeComparisonToArray PhanTypeConversionFromArray PhanTypeErrorInInternalCall PhanTypeInstantiateAbstractStatic PhanTypeInstantiateTraitStaticOrSelf PhanTypeMagicVoidWithReturn PhanTypeMismatchArgumentNullable PhanTypeMismatchArgumentNullableInternal PhanTypeMismatchPropertyByRef PhanTypeObjectUnsetDeclaredProperty PhanTypePossiblyInvalidCloneNotObject PhanTypeVoidAssignment PhanUnanalyzable PhanUnanalyzableInheritance PhanUndeclaredInvokeInCallable PhanUndeclaredMagicConstant PhanUndeclaredVariableAssignOp PhanUndeclaredVariableDim PhanUnextractableAnnotation PhanUnextractableAnnotationElementName PhanUnextractableAnnotationPart PhanUnextractableAnnotationSuffix PhanUnusedGotoLabel PhanUnusedVariableCaughtException PhanUnusedVariableValueOfForeachWithKey PhanUseNormalNamespacedNoEffect PhanUselessBinaryAddRight PhanVariableDefinitionCouldBeConstant PhanVariableDefinitionCouldBeConstantEmptyArray PhanVariableDefinitionCouldBeConstantFalse PhanVariableDefinitionCouldBeConstantFloat PhanVariableDefinitionCouldBeConstantInt PhanVariableDefinitionCouldBeConstantNull PhanVariableDefinitionCouldBeConstantString PhanVariableDefinitionCouldBeConstantTrue

highlight link phanCritical Error
highlight link phanNormal Todo
highlight link phanLow Comment